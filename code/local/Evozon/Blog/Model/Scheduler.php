<?php

/**
 * Schedule Data model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Scheduler extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function _construct()
    {
        $this->_init('evozon_blog/scheduler');
    }
    
    /**
     * Delete all posts with published status
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $postData
     */
    public function deleteByPostAndStoreIds($postData)
    {
        $this->_getResource()->deleteByPostAndStoreIds($postData);
    }
    
    /**
     * Prepare scheduler data
     * 
     * @param Varien_Object $post
     * @return \Evozon_Blog_Model_Scheduler
     */
    public function init(Varien_Object $post)
    {
        $this
            ->setPostId($post->getId())
            ->setStoreId($post->getStoreId())
            ->setTime($post->getPublishDate())
            ->setNextStatus(Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED);
        
        return $this;
    }
}
