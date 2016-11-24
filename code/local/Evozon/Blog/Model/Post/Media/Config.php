<?php

/**
 * Media config
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Media_Config extends Mage_Catalog_Model_Product_Media_Config
{
    const EVOZON_BLOG_POST_MEDIA_URL = 'evozon/blog/post';
    
    /**
     * Filesystem directory path of post images
     * relatively to media folder
     *
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBaseMediaPathAddition()
    {
        return 'evozon' . DS . 'blog' . DS . 'post';
    }

    /**
     * Web-based directory path of post images
     * relatively to media folder
     *
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBaseMediaUrlAddition()
    {
        return self::EVOZON_BLOG_POST_MEDIA_URL;
    }
    
    /**
     * Return media path
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBaseMediaPath()
    {
        return Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . $this->getBaseMediaPathAddition();
    }

    /**
     * Return media url
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBaseMediaUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $this->getBaseMediaUrlAddition();
    }
}
