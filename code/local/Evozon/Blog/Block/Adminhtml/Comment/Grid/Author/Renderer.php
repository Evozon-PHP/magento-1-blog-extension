<?php

/**
 * Author Renderer
 * Display the information about the author from the customer or admin table 
 * instead of the comment table
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid_Author_Renderer 
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    /**
     * Display the proper name and email about the author
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Comment $row
     * @return string
     */
    public function render(Varien_Object $row)
    {            
        // get author data
        $author = $row->getAuthor();
        
        // concatenate the author name or link to admin/customer account with the email        
        $value = $author->getBackendLink() . '<br>' .
            '<a href="mailto:' . $author->getEmail() . '">' . 
                $author->getEmail() . 
            '</a> ';
        
        return $value;
    }
}
