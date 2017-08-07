<?php

/**
 * Comments frontend controller
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_CommentController extends Mage_Core_Controller_Front_Action
{

    /**
     * Index action for Customer Blog Comments
     */
    public function indexAction()
    {
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('customer/account/login');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Save the new comment in db
     * The comments are received via AJAX
     * After FE validation, the comment status will be set according to configurations
     * Depending on the status, the proper response will be set
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function saveAction()
    {
        $inputData = $this->getRequest()->getPost();

        if (!$this->getRequest()->isAjax()) {
            $this->_forward('no-route');
            return false;
        }

        $comment = Mage::getSingleton('evozon_blog/comment');

        try {
            $this->filter($comment, $inputData);

            $comment->setStatus($comment->getStatusForFrontendComments());
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $comment->setUserContext(true);
            }

            $comment->save();

            $response = $this->setResponseMessage($comment);
        } catch (Evozon_Blog_Model_Exception_Validate $exc) {
            $response['error'] = $exc->getMessagesArray();
        } catch (Exception $ex) {
            $response['error']['form_key'] = $ex->getMessage();
            Mage::logException($ex);
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

    /**
     * Presentation layer validation
     * It will trim, striptag and remove HtmlEntities from the input
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Comment $comment
     * @param array $input
     * @return array
     * @throws Evozon_Blog_Model_Exception_Validate
     */
    protected function filter($comment, $input)
    {
        $validator = Mage::getModel('evozon_blog/filter_factory', 
            array(
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_TYPE => Evozon_Blog_Model_Filter_Config::XML_PATH_BLOG_VALIDATION_TYPE_COMMENTS,
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_INPUT => $input
            )
        );

        if ($validator->hasMessages()) {
            throw new Evozon_Blog_Model_Exception_Validate($validator->getMessages());
        }

        $comment->setData($validator->getEscaped());

        return $this;
    }

    /**
     * After a comment has been saved, the response has to be send back to user
     * Depending on the received status, the following up response will be set
     * -if the message is automatically approved: we have to set the comments url & return comments block again
     * -if it has a different status - just the message will be sent
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param \Evozon_Blog_Model_Comment $comment
     */
    protected function setResponseMessage($comment)
    {
        $status = $comment->getStatus();

        if ($status == Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED) {
            return $this->_getApprovedResponse($comment);
        }

        return $this->_getResponseMessage($status);
    }

    /**
     * If the comment is automatically approved, display the list of comments
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Comment 
     * @return array
     */
    protected function _getApprovedResponse($comment)
    {
        $response = array();
        $response['url'] = $comment->getCommentUrl();

        // create the comments block and set the post and the child block (reply block)
        $response['comments'] = $this->getLayout()
            ->createBlock('evozon_blog/post_view_comments_list')
            ->setPostId($comment->getPostId())
            ->setChild(
                'evozon_blog_post_comments_reply', Mage::getBlockSingleton('evozon_blog/post_view_comments_reply')
                ->setPostId($comment->getPostId())
            )
            ->toHtml();

        return $response;
    }

    /**
     * Based on the status, the comment response message from the system shall be retrieved from model
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $status
     * @return array
     */
    protected function _getResponseMessage($status)
    {
        $response = array();
        $response['message'] = Mage::getSingleton('evozon_blog/adminhtml_comment_status')
            ->getUserMessageOnCommentSubmitArray($status);

        return $response;
    }

    /**
     * Notify customer action
     *
     * @return bool
     */
    public function notifyCustomerAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('no-route');
            return false;
        }
        $commentId = $this->getRequest()->getParam('commentId');
        $notifyCustomer = $this->getRequest()->getParam('notifyCustomer');
        $response = array();
        $response['success'] = false;

        $comment = Mage::getModel('evozon_blog/comment')->load($commentId);

        if ($comment && $comment->getId()) {
            try{
                $comment->setNotifyCustomer($notifyCustomer);
                $comment->save();
                $response['success'] = true;
            } catch (Exception $e){
                $response['error'] = $e->getMessage();
            }
        } else {
            $response['error'] = $this->__('Invalid comment');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
    
    /**
     * Check by email if customer already exists
     *
     * @return bool
     */
    public function isCustomerAction()
    {
        $inputData = $this->getRequest()->getPost();
        
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('no-route');
            return false;
        }
        
        $url = Mage::getUrl('customer/account/login');
        $response = array();
        $response['is_customer'] = true;
        $response['message'] = $this->__('This email address is already registered in our system. Please <a href="%s">login</a> if you want to leave a comment', $url);
        $email = $inputData['email'];
        
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email);
        
        if (!$customer || !$customer->getId()) {
            $response['is_customer'] = false;
        }
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}