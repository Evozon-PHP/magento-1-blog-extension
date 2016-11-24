<?php

/**
 * Post title Renderer
 * Display the post title as link to the post
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid_Post_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Display the proper parent value for comment
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Comment $row
     * @return string
     */
    public function render(Varien_Object $row)
    {       
        // get the value from the grid
        $value = $row->getData($this->getColumn()->getIndex());

        // create the link to post comments 
        $postTitleLink = '<a href="' . Mage::helper("adminhtml")->getUrl('*/blog_post/edit', ['id' => $row->getPostId(), 'active_tab'=>'comments']) . '">' . $value . '</a>';
        
        return $postTitleLink;
    }
}
