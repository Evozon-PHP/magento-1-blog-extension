<?php

/**
 * Reply Block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Post_View_Comments_Reply extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * Built-in constructor. Set the template, if not already set.
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct()
    {
        $template = $this->getTemplate();
        if (empty($template)) {
            $this->setTemplate('evozon/blog/post/view/comments/reply.phtml');
        }
    }
    
    /**
     * Accessing validation config parameters
     * 
     * @param string $key
     * @return string | int
     */
    public function getConfig($key)
    {
        return Mage::getSingleton('evozon_blog/config')->getCommentsValidationConfig($key);
    }

}
