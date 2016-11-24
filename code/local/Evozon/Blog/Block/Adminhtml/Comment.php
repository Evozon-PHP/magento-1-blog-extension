<?php

/**
 * Grid block for comment entities
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Overwrite grid constructor
     *  
     * set the controller and block
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. evozon_blog/adminhtml_comment
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_comment';
        $this->_headerText = $this->__('Comments');
         
        parent::__construct();
        
        $this->_removeButton('add');
    }
}
