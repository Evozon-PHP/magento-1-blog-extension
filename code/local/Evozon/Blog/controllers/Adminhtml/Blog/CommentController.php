<?php

/**
 * List, Edit, Add New and Mass actions for comments.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Blog_CommentController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Default action
     * show comment's grid
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Initialize action
     *
     * Set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('evozon_blog/evozon_comment')
            ->_title($this->__('BLOG'))->_title($this->__('Comments'));

        return $this;
    }

    /**
     * Edit commmt action
     *
     * render form and data to be edited
     *
     * @return NULL if the id provided cannot be loaded
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function editAction()
    {
        // Get id if available
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('evozon_blog/comment');

        if ($id) {
            $model->load($id);

            // Check if record is loaded
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('This comment does not exist anymore.'));
                $this->_redirect('*/*/');

                return null;
            }
        }

        $this->_title($model->getName());
        $data = Mage::getSingleton('adminhtml/session')->getCommentData(true);
        if (!empty($data)) {
            $model->setData('edit', $data);
        }

        Mage::register('evozon_blog_comment', $model);

        $this->_initAction()
            ->_addBreadcrumb($this->__('Edit Comment'), $this->__('Edit Comment'))
            ->_addContent($this->getLayout()->createBlock('evozon_blog/adminhtml_comment_edit')
                ->setData('action', $this->getUrl('*/*/save'))
            )
            ->renderLayout();
    }

    /**
     * Save comment action
     * Save data into database
     * The subcomments added from be panel are only admin comments
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function saveAction()
    {
        $data = new Varien_Object();
        $data->setData($this->getRequest()->getPost());

        if (empty($data)) {
            $this->_getSession()
                ->addError($this->__('No data has been found for this comment. Try again!'));
            $this->_redirect('*/*/edit');
        }

        $error = true;

        // create the model and set the comment data on the model
        $userComment = Mage::getSingleton('evozon_blog/comment')
            ->load($this->getRequest()->getParam('id'));

        $adminCommentData = new Varien_Object();
        if ($data->hasSubcomment()) {
            $adminCommentData->setData($data->getSubcomment());
            $adminCommentData->setFormKey($data->getFormKey());
            $data->unsetData('subcomment');
        }

        try {
            $filteredData = $this->filter($data);
            //Update Model Data
            foreach ($filteredData as $key => $value) {
                $userComment->setData($key, $value);
            }
            $userComment->save();

            $replyContent = $adminCommentData->getContent();
            if (!empty($replyContent)) {
                $adminCommentData->setParentId($userComment->getId());
                $this->_saveAdminReply($adminCommentData);
                $this->_getSession()->addSuccess($this->__('The reply has been saved. '
                        . 'You will find it in the \'Sub-Comments\' section.'));
            }

            $error = false;
            $this->_getSession()->addSuccess($this->__('The comment has been updated.'));
        } catch (Evozon_Blog_Model_Exception_Validate $exc) {
            foreach ($exc->getExceptionMessages() as $message) {
                $this->_getSession()->addError($message);
            }
        } catch (Exception $exc) {
            $this->_getSession()->addError($this->__('An error occurred while saving this comment.'));
            Mage::logException($exc);
        }

        if ($error || 'edit' == $this->getRequest()->get('back')) {
            $this->_getSession()->setCommentData($data);
            $this->_redirect('*/*/edit', array(
                'id' => $userComment->getId(),
                '_current' => true
                )
            );
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Process and save reply comment
     * The errors will be caught in the save function of the controller
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $parentId
     * @param array $content
     */
    protected function _saveAdminReply($content)
    {
        $adminComment = Mage::getModel('evozon_blog/comment');
        $adminComment->setAdminContext(true);

        $data = $this->filter($content);
        //Update Model Data
        foreach ($data as $key => $value) {
            $adminComment->setData($key, $value);
        }
        $adminComment->save();
    }

    /**
     * Presentation layer validation
     * It will trim, striptag and remove HtmlEntities from the input
     * It will check that the default value has been set
     *
     * @param $input
     *
     * @return array
     * @throws Evozon_Blog_Model_Exception_Validate
     */
    protected function filter($input)
    {
        $validator = Mage::getModel('evozon_blog/filter_factory', 
            array(
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_TYPE => Evozon_Blog_Model_Filter_Config::XML_PATH_BLOG_VALIDATION_TYPE_COMMENTS,
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_INPUT => $input->getData()
            )
        );

        if ($validator->hasMessages()) {
            throw new Evozon_Blog_Model_Exception_Validate($validator->getMessages());
        }

        return $validator->getEscaped();
    }

    /**
     * Delete comment action
     * delete comment from database
     *
     * It can be deleted from 2 views: post edit (comments tab) or comment edit
     * The redirect has to be accordingly
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function deleteAction()
    {
        $_helper = Mage::helper('evozon_blog');

        // delete specific comment
        $commentId = $this->getRequest()->get('id');
        if (empty($commentId) || !is_numeric($commentId)) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('No comment id provided for delete action!'));
            $this->_redirect('*/*/');
            return $this;
        }

        // load comment and delete.
        $commentModel = Mage::getModel('evozon_blog/comment');
        $commentModel->load($commentId);
        try {
            $commentModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($_helper->__('Comment with id %s has been deleted!', $commentId));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('An error occurred while deleting the comment.'));
            Mage::logException($ex);
        }

        $this->redirect();
    }
    
    
    /**
     * Redirecting to required views (comments tab on post edit, subcomments tab on comment edit or back to comments grid)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return redirect
     */
    protected function redirect()
    {
        $post = $this->getRequest()->get('post_id');
        $comment = $this->getRequest()->get('comment_id');
        
        if ($post)
        {
            return $this->_redirect('*/blog_post/edit', array('id' => $post, 'active_tab' => 'comments'));
        }
        
        if ($comment)
        {
            return $this->_redirect('*/*/edit', array('id' => $comment, 'active_tab' => 'subcomments'));
        }
        
        $this->_redirect('*/*/index');
    }

    /**
     * Update comment(s) status action
     * Displays warning if no comment has been selected
     * Displays error/succes messages in case of error/succes
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massStatusAction()
    {
        //getting the ids of the comments to apply the massaction to
        $ids = $this->getRequest()->getParam('comment_ids');

        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Comment(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    Mage::getSingleton('evozon_blog/comment')
                        ->load($id)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                //displying the success message
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated.', count($ids))
                );
            } catch (Exception $e) {
                //displaying the error for updating the comments message
                $this->_getSession()->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Deleting the comment(s) by massaction
     * Displays warning if no comment has been selected
     * Displays warnings for error/success of deleting the selected comments
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massDeleteAction()
    {
        //getting the ids
        $ids = $this->getRequest()->getParam('comment_ids');

        try {

            if (!is_array($ids)) {
                throw new Exception('Please select Comment(s)');
            }
            foreach ($ids as $id) {
                Mage::getSingleton('evozon_blog/comment')
                    ->load($id)
                    ->delete();
            }
            //displaying success message
            $this->_getSession()->addSuccess(
                $this->__('Total of %d record(s) were successfully deleted.', count($ids))
            );
        } catch (Exception $e) {
            //displaying the error at delete message
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Ajax action 
     * Set to the response a json with all the subcomments for a comment/subcomment
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function subcommentsAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return null;
        }

        // verify if the post param is defined
        $commentId = $this->getRequest()->getPost('commentId');
        if (!$commentId) {
            return null;
        }

        $postId = (int) $this->getRequest()->getParam('post_id');
        $commentsCount = $this->getRequest()->getPost('subcommentsNr');

        // if exist subcomments display the info about them
        if ($commentsCount > 0) {
            $subcommentsBlock = $this->getLayout()
                ->createBlock('evozon_blog/adminhtml_comment_list')
                ->setCommentId($commentId)
                ->setPostId($postId)
                ->setIsAjax(true)
                ->toHtml();

            // set block to json response
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($subcommentsBlock));
        }
    }

    /**
     * Ajax action to change the status of the comment
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function changeStatusAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return null;
        }

        //get the comment id and the new status from request
        $commentId = $this->getRequest()->getPost('commentId');
        $status = $this->getRequest()->getPost('status');

        // verify if the post param is defined or is not numeric
        if (empty($commentId) || !is_numeric($commentId) || !is_numeric($status)) {
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode('ERROR'));
        }

        $commentModel = Mage::getModel('evozon_blog/comment')->load($commentId);
        if ($commentModel->getId()) {
            $commentModel->setStatus($status);

            try {
                $commentModel->save();
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode('SUCCESS'));
            } catch (Exception $ex) {
                Mage::logException($ex);
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode('ERROR'));
            }
        }
    }

}
