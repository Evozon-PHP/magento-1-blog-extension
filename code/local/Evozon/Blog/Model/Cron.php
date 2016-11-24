<?php

/**
 * Model for cron actions
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Cron extends Mage_Core_Model_Abstract 
{
    /**
     * Create a comments collection with status SPAM and delete each row
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function deleteSpamComments()
    {        
        /* @var $collection Evozon_Blog_Model_Comment */
        $model = Mage::getModel('evozon_blog/comment');

        // delete comments by spam status
        try {
            $model->deleteBySpamStatus();
        } catch (Exception $ex) {
            Mage::log($ex->getMessage());
        }
    }
    
    /**
     * Change the posts status and delete from schedule_data the corresponding entries
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function changePostsStatus()
    {        
        // array with the posts id which should be deleted
        $postData = array();
        
        // get collection
        $collection = Mage::getResourceModel('evozon_blog/scheduler_collection');
        // filter the collection by published date
        $collection->addFieldToFilter('time', array('lt' => now()));
        
        // change the status for all the posts
        foreach ($collection as $scheduleData) {            
            Mage::getResourceModel('evozon_blog/post_action')->updateAttribute(
                array($scheduleData->getPostId()), 
                array('status' => $scheduleData->getNextStatus()),
                $scheduleData->getStoreId()
            );
            
            $postData[] = array('post_id' => $scheduleData->getPostId(), 'store_id' => $scheduleData->getStoreId());
        }
        
        try {
            Mage::getModel('evozon_blog/scheduler')->deleteByPostAndStoreIds($postData);
        } catch (Exception $ex) {
            Mage::log($ex->getMessage());
        }
    }
}
