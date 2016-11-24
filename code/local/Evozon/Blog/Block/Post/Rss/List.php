<?php

/**
 * Class used to add Post Rss to Misc Rss category list in magento
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Rss_List extends Mage_Rss_Block_List
{
    /**
     * Check if block is enabled in system config
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @return boolean
     */
    public function isEnabled()
    {        
        return (bool) $this->getConfigModel()->getPostRssConfig(Evozon_Blog_Model_Config_Post::RSS_ENABLED);
    }
    
    /**
     * Return the action for rss
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function getRssUrlAction()
    {
        return Mage::getUrl('blog/rss/', array('store' => Mage::app()->getStore()->getId()));
    }
    
    /**
     * Return the config model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Config
     */
    public function getConfigModel()
    {
        return Mage::getSingleton('evozon_blog/config');
    }
}
