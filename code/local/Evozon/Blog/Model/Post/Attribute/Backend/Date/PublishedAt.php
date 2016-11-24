<?php

/**
 * Published at backend model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Backend_Date_PublishedAt extends Evozon_Blog_Model_Post_Attribute_Backend_Date_Abstract
{
    /**
     * Set published date
     * Set published date in UTC time zone
     *
     * @param Mage_Core_Model_Object $object
     * @return Evozon_Blog_Model_Post_Attribute_Backend_Date_PublishedAt
     */
    public function beforeSave($object)
    {
        $publishedAt = $this->setPublishedDateByStatus($object);
        $attributeCode = $this->getAttribute()->getAttributeCode();
        
        $object->setData($attributeCode, $publishedAt);

        return $this;
    }

    /**
     * Set the published date by status
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Post $object
     */
    protected function setPublishedDateByStatus($object)
    {
        $status = $object->getStatus();

        if ($status == Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED) {
            return $this->setPublishingDateToPublished($object);
        }

        if ($status == Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PENDING) {
            return $this->setPublishingDateToPending($object);
        }
    }

    /**
     * Set the published date if the pending status was selected
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    protected function setPublishingDateToPending($object)
    {
        $scheduledDate = $object->getScheduledDate();
        $scheduleTime = $object->getScheduledTime();

        // verify if the date inputs are not empty
        if (empty($scheduledDate) || empty($scheduleTime)) {
            return null;
        }

        $date = $scheduledDate . ' ' . implode(':', $scheduleTime);
        $zendDate = Mage::app()->getLocale()->utcDate(null, $date, true, $this->_getFormat($date));

        // set the post date in the correct format
        $publishDate = $zendDate->toString('YYYY-MM-dd HH:mm:ss');

        // verify if publish date is less than current date to show a notice for the user
        if (strtotime($publishDate) < strtotime(now())) {
            Mage::getSingleton('adminhtml/session')->addNotice(
                Mage::helper('evozon_blog')->__('The publish date selected is in the past. The post will be published on the next cron execution.')
            );
        }

        return $publishDate;
    }

    /**
     * Set the published date if the published status was selected
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    protected function setPublishingDateToPublished($object)
    {
        // if the old status was published keep the old published date
        if ($object->getOrigData('status') == Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED) {
            return $object->getOrigData('publish_date');
        }

        return now();
    }

}
