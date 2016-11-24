<?php

/**
 * Comments block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_List extends Mage_Adminhtml_Block_Template
{

    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Template naming
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/comment/list.phtml';
    }

    /**
     * Return an array with all the comments that are on the first level if post id is defined 
     * else if comment id is defined return an array with its subcomments (order by created at date)
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $postId
     * @return array
     */
    public function getComments()
    {
        $commentsCollection = Mage::getModel('evozon_blog/comment')->getCollection();

        if ($this->viewSubcommentsFromPostEdit() || $this->getCommentId()) {
            $commentsCollection->getChildrenCommentsByParentId($this->getCommentId());
        }

        if ($this->getPostId() && !$this->viewSubcommentsFromPostEdit()) {
            $commentsCollection->getChildrenCommentsByPostId($this->getPostId());
        }

        $commentsCollection
            ->addCustomerAndAdminJoin()
            ->addSubcommentsCountData()
            ->setProperDateFormat();

        return $commentsCollection->getItems();
    }

    /**
     * Checking if the list is rendered to display subcomments from blog post edit
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    protected function viewSubcommentsFromPostEdit()
    {
        return $this->getPostId() && $this->getCommentId();
    }

    /**
     * Verify if a comment has subject
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Comment $comment
     * @return bool
     */
    public function hasSubject($comment)
    {
        if ($comment->getSubject()) {
            return true;
        }

        return false;
    }

    /**
     * URL to view comment`s subcomments (if there are any*)
     * 
     * @return string
     */
    public function getSubcommentsUrl()
    {
        return Mage::helper("adminhtml")->getUrl('*/blog_comment/subcomments', array(
                'post_id' => $this->getPostId()));
    }

    /**
     * URL to changing the status of a comment
     * 
     * @return string
     */
    public function getChangeStatusUrl()
    {
        return Mage::helper("adminhtml")->getUrl('*/blog_comment/changeStatus');
    }

    /**
     * Accessing comment`s status options in order to edit them directly
     * 
     * @return array
     */
    public function getCommentStatusOptions()
    {
        return Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getOptionArray();
    }

    /**
     * Edit url for comment
     * 
     * @param int $commentId
     * @return string
     */
    public function getEditUrl($commentId)
    {
        return Mage::helper("adminhtml")->getUrl("*/blog_comment/edit", array('id' => $commentId));
    }

    /**
     * Delete url for comment
     * 
     * @param int $commentId
     * @return string
     */
    public function getDeleteUrl($commentId)
    {
        if ($this->viewSubcommentsFromPostEdit()) {
            return Mage::helper("adminhtml")->getUrl("*/blog_comment/delete", array(
                    'id' => $commentId,
                    'post_id' => $this->getPostId()
                    )
            );
        }

        return Mage::helper("adminhtml")->getUrl("*/blog_comment/delete", array(
                'id' => $commentId,
                'post_id' => $this->getPostId(),
                'comment_id' => $this->getCommentId()
                )
        );
    }

    /**
     * In case we are on a post and there are no comments, 
     * the no comments message will be displayed
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return boolean
     */
    public function showNoCommentsMessage()
    {
        if (!$this->hasComments()) {
            return false;
        }

        if ($this->getComments()) {
            return false;
        }

        return true;
    }

}
