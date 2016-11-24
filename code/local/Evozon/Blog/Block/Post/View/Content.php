<?php

/**
 * Create the block for content
 *
 * @package     Evozon_Blog
 * @author      Lilian Codreanu <lilian.codreanu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_View_Content extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();

        $helper = Mage::helper('cms');

        $processor = $helper->getPageTemplateProcessor();
        $html = $processor->filter($html);
        $html = $this->getMessagesBlock()->toHtml() . $html;

        return $html;
    }

    /**
     * Return the content of the post
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function getPostContent()
    {
        // decode the content
        $content = html_entity_decode($this->getPost()->getPostContent(), ENT_QUOTES);
        
        // return the post content
        return $content;
    }
}