<?php

/**
 * Image block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Post_Image extends Evozon_Blog_Block_Post_Abstract
{    
    /**
     * Get post image for
     *  - listing: small_image
     *  - post view: image
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return String
     */
    protected function getPostImage()
    {
        // get the post from registry
        $post = $this->getPost();

        // get image for listing
        $image = (string)$this->getImageHelper()->init($post, 'small_image')->keepFrame(false)->resize(500);

        // check if we're in post single page and set the main image
        if (Mage::registry('blog_post')) {
            $image = (string)$this->getImageHelper()->init($post, 'image')->keepFrame(false)->resize(680);
        }

        return $image;
    }
}
