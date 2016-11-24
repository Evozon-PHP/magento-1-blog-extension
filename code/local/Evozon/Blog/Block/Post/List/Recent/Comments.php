<?php

/**
 * Recent comments widget, will show last x comments
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_List_Recent_Comments extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * will be populated with comments
     * 
     * @var Evozon_Blog_Model_Resource_Comment_Collection
     */
    protected $_commentsCollection = null;

    /**
     * will be populated with config values
     * 
     * @var array 
     */
    protected $_configValues = array();

    /**
     * constructor, set defaults
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->getIsEnabled()) {
            $this->setTemplate('evozon/blog/post/list/recent/comments.phtml');
        }
        
        $this->setConfigValues();
    }
    
    /**
     * Getting is enabled status from config
     * 
     * @return bool
     */
    public function getIsEnabled()
    {        
        return $this->getDataSetDefault('is_enabled', $this->getStoreConfig(Evozon_Blog_Model_Config_Comment::RECENT_COMMENTS_ENABLED));
    }

    /**
     * get store config using constant prefix and given field name
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @param String $field
     * @return String
     */
    protected function getStoreConfig($field)
    {
        return $this->getConfigModel()->getCommentsConfig($field);
    }

    /**
     * Set the config data
     * if config not set in widget's option read from system's config
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function setConfigValues()
    {
        $values = array(
            'comments_no' => $this->getCommentsNumber(),
            'words_no' => $this->getCommentMaxWords()
        );

        if (!$this->getCustomConfig()) {
            $values = array(
                'comments_no' => $this->getStoreConfig(Evozon_Blog_Model_Config_Comment::RECENT_COMMENTS_NUMBER),
                'words_no' => $this->getStoreConfig(Evozon_Blog_Model_Config_Comment::RECENT_COMMENTS_WORDS_NUMBER)
            );
        }

        $this->_configValues = $values;
    }
    
    /**
     * Return the config data
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @param string $configType
     * @return array | string
     */
    public function getConfigValues($configType = null)
    {
        if (!is_null($configType)) {
            return $this->_configValues[$configType];
        }

        return $this->_configValues;
    }

    /**
     * Returns comments collection joined with posts filtered by status and sorted descendent
     * Only the approved comments from publish posts are displayed
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $limit
     * @return Evozon_Blog_Model_Resource_Comment_Collection
     */
    protected function _getCommentsCollection($limit = 0)
    {
        if (!$this->_commentsCollection) {

            $collection = Mage::getModel('evozon_blog/comment')->getCollection();

            $collection->addPostDetails()
                ->addFieldToFilter('status.value', Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED)
                ->addFieldToFilter('status', Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED)
                ->setOrder('modified_at', 'DESC');

            if ($limit > 0) {
                $collection->setPageSize($limit);
            }

            $this->_commentsCollection = $collection;
        }

        return $this->_commentsCollection->load();
    }

    /**
     * Method used in template to get comments as array after content has been truncated
     * Cast comments_no to int and if > 0 populate $comments
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @return null|Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function getComments()
    {
        $comments = null;   
        $configCommentsNo = $this->getConfigValues('comments_no');

        if ((int) $configCommentsNo > 0) {
            $comments = $this->_getCommentsCollection($configCommentsNo);
        }

        return $comments;
    }
}
