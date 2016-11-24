<?php

/**
 * Parent Renderer
 * Display the link to the parent comment or - is the comment hasn't parent
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid_Parent_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
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

        // verify if the value is greater than 0 and display the link to parent
        // else (if the comment hasn't parent) display -
        if ($value > 0) {
            $value = '<a href="' . Mage::helper("adminhtml")->getUrl('*/blog_comment/edit', ['id' => $value]) .  '">' . Mage::helper('evozon_blog')->__('Parent') . ': ' . $value . '</a>';
        } else {
            $value = '-';
        }
        
        return $value;
    }
}
