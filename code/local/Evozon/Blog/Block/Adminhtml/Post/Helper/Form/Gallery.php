<?php

/**
 * Post helper form for gallery
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Helper_Form_Gallery extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery
{        
    /**
     * Set the content block
     *
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getContentHtml()
    {
        /* @var $content Evozon_Blog_Block_Adminhtml_Post_Helper_Form_Gallery_Content */
        $content = Mage::getSingleton('core/layout')
            ->createBlock('evozon_blog/adminhtml_post_helper_form_gallery_content');

        $content->setId($this->getHtmlId() . '_content')->setElement($this);

        return $content->toHtml();
    }
}
