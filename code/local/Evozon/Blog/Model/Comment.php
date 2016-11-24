<?php

/**
 * Comment model
 * According to the comment model,
 * The comment has 4 major parts:
 * -> author data ((user_id || admin_id || (name && e-mail for a guest)) && author_ip)
 * -> context data (parent_id, path, level)
 * -> content data (subject, content)
 * -> status data (status, enabled)
 *
 * In order to be saved, the following have data has to be stored:
 * -> if the object is new, the author data is set and default (store_id, post_id, created_at)
 * 
 * From FE Controller it is set the InputData (consisting of: content, subject, author_email, author, post_id, etc..)
 * 
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Comment extends Evozon_Blog_Model_Abstract
{

    /**
     * observer prefix
     * 
     * @var string 
     */
    protected $_eventPrefix = 'evozon_blog_comment';

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('evozon_blog/comment');
    }

    /**
     * Set created at property before save, 
     * Spam check service is called by observer
     * also the path and the level
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @see    Evozon_Blog_Model_Comment_Observer 
     * @return Evozon_Blog_Model_Comment
     */
    protected function _beforeSave()
    {
        parent::_beforeSave($this);

        if ($this->isObjectNew()) {
            $this->_setAuthorContext($this);

            $this->setCreatedAt(now());
            $this->setStoreId(Mage::app()->getStore()->getId());
        }

        $this->setEnabled($this->isEnabled());
        if ($this->dataHasChangedFor('status')) {
            $this->setChangeStatusForChildren(true);
        }

        $this->setModifiedAt(now());

        return $this;
    }


    /**
     * Creates a collection of comments used to generate the threads for the user comments
     * displayed in his My Blog Comments tab from account
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $parentIds
     * @param array $commentIds
     * @return this_collection
     */
    public function getThreadCollection(array $parentIds, array $commentIds)
    {
        return $this->getResourceCollection()
            ->addFieldToSelect(array('subject', 'post_id', 'content', 'created_at', 'user_id', 'admin_id', 'author', 'parent_id'))
            ->addFieldToFilter(array('main_table.id', 'main_table.parent_id'), array(array('in' => $parentIds), array('in' => $commentIds)))
            ->addFieldToFilter('main_table.status', Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED)
            ->addCustomerAndAdminJoin()
            ->setOrder('created_at', 'DESC')
            ->setProperDateFormat();
    }

    /**
     * Besides author and author_email, the Author Ip has to be set
     * If the context of saving the context is not from a user or admin,
     * the other required data (author && author_email has been set via controller)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Comment $comment
     * @return \Evozon_Blog_Model_Comment
     */
    protected function _setAuthorContext($comment)
    {
        if ($comment->hasUserContext()) {
            $comment->setUserId(Mage::getSingleton('customer/session')->getCustomer()->getId());
        }

        if ($comment->hasAdminContext()) {
            $comment->setAdminId(Mage::getSingleton('admin/session')->getUser()->getId());
        }

        $comment->setAuthorIp(Mage::helper('core/http')->getRemoteAddr());

        return $this;
    }

    /**
     * Check if entity is enabled
     * 
     * @return boolean
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function isEnabled()
    {
        $enabled = false;
        if ($this->getStatus() == Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED) {
            $enabled = true;
        }

        return $enabled;
    }
    
    /**
     * Return the nr of first level subcomments for the current comment
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return int
     */
    public function getFirstLevelCount()
    {
        return $this->_getResource()->getFirstLevelSubcommentsCountForComment((int) $this->getId());
    }

    /**
     * Get url to post object, to the list of post comments
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @see    Evozon_Blog_Model_Resource_Comment_Collection
     * @return string
     */
    public function getCommentUrl()
    {
        return $this->getPostUrl() . '#comment-' . $this->getId();
    }

    /**
     * Getting post url from comment
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getPostUrl()
    {
        return Mage::getBaseUrl() . $this->_getPostUrl();
    }

    /**
     * Getting post url from indexer resource
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string | null
     */
    protected function _getPostUrl()
    {
        $object = new Varien_Object(array('id'=> $this->getPostId(), 'store_id'=>Mage::app()->getStore()->getId()));
        $url = Mage::getSingleton('evozon_blog/factory')
            ->getPostUrlInstance()
            ->setPost($object)
            ->getRequestPath();

        return $url;
    }

    /**
     * Set the status for the comments added in frontend
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getStatusForFrontendComments()
    {
        // if the comments are automatic approved set the approved status
        if ($this->getConfigModel()->getCommentsConfig(Evozon_Blog_Model_Config_Comment::GENERAL_AUTOMATIC_APPROVAL)) {
            return Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED;
        }

        return Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_PENDING;
    }

    /**
     * Delete comments by spam status
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function deleteBySpamStatus()
    {
        $this->_getResource()->deleteBySpamStatus();
    }

    /**
     * Accessing validator config
     * 
     * @return Evozon_Blog_Model_Filter_Config
     */
    public function getValidatorConfig()
    {
        return Mage::getSingleton('evozon_blog/filter_config');
    }
    
}
