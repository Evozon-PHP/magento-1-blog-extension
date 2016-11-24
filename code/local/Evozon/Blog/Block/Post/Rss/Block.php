<?php

/**
 * Rss block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Post_Rss_Block extends Evozon_Blog_Block_Post_Abstract
    implements Mage_Widget_Block_Interface
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Return template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/rss/block.phtml';
    }
    
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
     * Return the rss url
     * 
     * @return string
     */
    public function getRssUrl()
    {
        return Mage::getUrl('blog/rss/', array('store' => Mage::app()->getStore()->getId()));
    }
}
