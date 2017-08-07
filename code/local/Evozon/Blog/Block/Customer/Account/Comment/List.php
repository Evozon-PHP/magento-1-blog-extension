<?php

/**
 * Customer accounts comments view and display
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Customer_Account_Comment_List extends Evozon_Blog_Block_Abstract
{
    const ENABLED = 1;
    /**
     * Comments collection
     *
     * @var Evozon_Blog_Model_Comment
     */
    protected $_commentsCollection = null;

    /**
     * It will contain parents and reply comments to every user comment in the collection
     *
     * @var array
     */
    protected $_threadComments = null;

    /**
     * Declaring the toolbar and setting the default filters and the available filters
     * not allowing the user to select how many comments to see
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();
        $collection = $this->_getCommentsCollection();

        $toolbar->setAvailableFilters($this->getCommentFilters());
        $toolbar->setLimit((int) Evozon_Blog_Model_Config::getCommentCustomerAccountLimitConfig());
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->setThreadComments();

        return parent::_beforeToHtml();
    }

    /**
     * Creating the toolbar block by the name
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Block_Comment_Toolbar
     */
    protected function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
    }

    /**
     * Get html code for toolbar
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChild('toolbar')->toHtml();
    }

    /**
     * Getting user comments
     * The user will be able to see the status
     * The comments collection is joined with the post model so that the title and link are accesible
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Comment
     */
    protected function _getCommentsCollection()
    {
        if (is_null($this->_commentsCollection)) {
            $commentsCollection = Mage::getModel('evozon_blog/comment')->getCollection();
            $commentsCollection->addFieldToSelect(array('post_id', 'subject', 'content', 'created_at',
                'user_id', 'author', 'parent_id', 'admin_id', 'status', 'notify_customer'))
                ->addFieldToFilter('main_table.user_id', $this->getCustomer()->getId())
                ->addFieldToFilter(
                    'main_table.status',
                    array('neq' => Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_SPAM)
                )
                ->addPostDetails()
                ->addSubcommentsCountData()
                ->setOrder('created_at', 'DESC');

            $this->_commentsCollection = $commentsCollection;
        }

        return $this->_commentsCollection;
    }

    /**
     * Accessing the loaded comments collection to display in the list
     * The proper date format is set here because it also "loads" the collection
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getLoadedCommentsCollection()
    {
        return $this->_getCommentsCollection()->setProperDateFormat();
    }

    /**
     * Creating the context of the user comment
     * We will be able to show parent comment or comment replies by extracting them from this array
     * The comment collection is filtered so that only the accepted comments are shown
     * The results are sorted by date
     *
     * The array looks like:  [ [user_comment_id][ID][comment->getData()] ], where ID = 'parent' for the parent and the rest- replies.
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function setThreadComments()
    {
        if (!is_null($this->_threadComments)) {
            return $this;
        }

        $loadedCollection = $this->getLoadedCommentsCollection();
        $commentIds = $loadedCollection->getAllIds();
        if (empty($commentIds))
        {
            return array();
        }

        $parentIds = $loadedCollection->getColumnValues('parent_id');
        if (empty($parentIds))
        {
            return array();
        }

        $collection = array();
        foreach ($loadedCollection as $comment) {
            $collection[$comment->getId()] = $comment->getParentId();
        }

        $threadComments = Mage::getModel('evozon_blog/comment')->getThreadCollection($parentIds, $commentIds);
        $threadCommentsArray[] = array();
        foreach ($threadComments as $item) {
            $item->setData('post_id', $item->getCommentUrl());
            if (in_array($item->getId(), $parentIds)) {
                $commentId = array_keys($collection, $item->getId());
                foreach ($commentId as $id) {
                    $threadCommentsArray[$id]['parent'] = $item->getData();
                }
            }

            if (in_array($item->getParentId(), $commentIds)) {
                $threadCommentsArray[$item->getParentId()][$item->getId()] = $item->getData();
            }
        }

        $this->_threadComments = $threadCommentsArray;
        return $this;
    }

    /**
     * Getting the thread comments for a user comment
     *
     * @param int $commentId
     * @return array
     */
    protected function getThreadComments($commentId)
    {
        if (isset($this->_threadComments[$commentId])) {
            return $this->_threadComments[$commentId];
        }

        return array(array('error' => $this->__('Requested messages are waiting for approval.')));
    }

    /**
     * Returning the parent comment
     * The parent comment is set as a ContextComment when the user will click to see the Parent Comment
     *
     * @param int $commentId
     * @return array
     */
    public function getParentComment($commentId)
    {
        if (isset($this->getThreadComments($commentId)['parent'])) {
            $parentComment['parent'] = $this->getThreadComments($commentId)['parent'];
            return $parentComment;
        }

        return array(array('error' => $this->__('The comment you have replied to has been removed or has been marked as spam!')));
    }

    /**
     * Getting the replies for each comment
     * If there is a parent comment (with index 0), we remove it from the multidimensional array
     *
     * @param int $commentId
     * @return array
     */
    public function getRepliesComments($commentId)
    {
        $repliesArray = $this->getThreadComments($commentId);
        if (isset($repliesArray['parent'])) {
            unset($repliesArray['parent']);
        }

        return empty($repliesArray) ? array(array('error' => $this->__('Requested messages are waiting for approval.'))) : $repliesArray;
    }

    /**
     * Getting the sorting filters array
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getCommentFilters()
    {
        return Mage::helper('evozon_blog')->getAvailableCommentFilters();
    }

    /**
     * Accessing parameters from system configurations
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $key
     * @return int|string
     */
    public function getConfigData($key = null)
    {
        return $this->getConfigModel()->getCommentsConfig($key);
    }

    /**
     * Getting customer data from the session or returning false in case nobody is logged in
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return boolean / Mage_Customer_Model_Customer
     */
    public function getCustomer()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()) {
            return $customer;
        }

        return false;
    }

    /**
     * Show | Hide button for notification by email
     *
     * @return bool
     */
    public function displayNotificationButton()
    {
        return (bool)$this->getConfigData(Evozon_Blog_Model_Config_Comment::XML_PATH_COMMENTS_NOTIFICATIONS);
    }

    /**
     * Defining which should be the new status of the notification
     *
     * @param $currentStatus
     * @return int
     */
    public function getNotificationStatusToggle($currentStatus)
    {
        if ($currentStatus)
        {
            return 0;
        }

        return 1;
    }

    /**
     * @return string
     */
    public function getNotifyCustomerUrl()
    {
        return Mage::getUrl('blog/comment/notifyCustomer');
    }
}