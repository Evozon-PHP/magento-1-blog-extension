<?php

/**
 * Post observer
 * It`ll load specific called layout.xml for the gallery
 *
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Post_Observer extends Evozon_Blog_Model_Abstract
{

    /**
     * Before load layout, check which one of the galleries needs to be uploaded
     * if the action name contains post_view add the update handle for post single page
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @event controller_action_layout_load_before
     * @param Varien_Event_Observer $observer
     * @return \Varien_Event_Observer
     */
    public function onControllerActionLayoutGetPostLayout(Varien_Event_Observer $observer)
    {
        $fullActionName = $observer->getEvent()->getAction()->getFullActionName();

        if (strpos($fullActionName, 'post_view') !== false) {
            $layoutUpdate = $observer->getEvent()->getLayout()->getUpdate();
            $layoutUpdate->addHandle('evozon_blog_removable_blocks');
            $singleViewLayout = $this->getConfigModel()->getGeneralConfig(Evozon_Blog_Model_Config_General::LAYOUT_POST_SINGLE_PAGE);
            $layoutUpdate->addHandle('evozon_blog_' . $singleViewLayout);
        }

        return $this;
    }

    /**
     * Change the posts status and delete from schedule_data the corresponding entries
     * 
     * @return \Evozon_Blog_Model_Post_Observer
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function changePostStatus()
    {
        $cron = Mage::getModel('evozon_blog/cron');
        $cron->changePostsStatus();
        
        return $this;
    }
}
