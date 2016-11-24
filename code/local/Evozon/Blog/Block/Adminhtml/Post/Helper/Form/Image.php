<?php

/**
 * Post form image field helper
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Helper_Form_Image extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Image
{
    /**
     * Return image url
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'evozon/blog/post/'. $this->getValue();
        }
        
        return $url;
    }
}
