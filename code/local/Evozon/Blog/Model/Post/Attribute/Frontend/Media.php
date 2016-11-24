<?php

/**
 * Post attribute frontend media
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Frontend_Media extends Mage_Catalog_Model_Product_Attribute_Frontend_Image
{
    /**
     * Return the image url
     * 
     * @param type $object
     * @param int $size
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getUrl($object, $size = null)
    {
        $url = false;
        $image = $object->getData($this->getAttribute()->getAttributeCode());
        
        if( !is_null($size) && file_exists(Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DS . 'evozon' . DS . 'blog' . DS . 'post' . DS . $size . DS . $image) ) {
            // resized image is cached
            return Mage::app()->getStore($object->getStore())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'evozon/blog/post/' . $size . '/' . $image;
        } 
        
        if( !is_null($size) ) {
            // resized image is not cached
            return Mage::app()->getStore($object->getStore())->getBaseUrl().'evozon/blog/post/image/size/' . $size . '/' . $image;
        } 
        
        if ($image) {
            // using original image
            return Mage::app()->getStore($object->getStore())->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'evozon/blog/post/' . $image;
        }
        
        return $url;
    }
}
