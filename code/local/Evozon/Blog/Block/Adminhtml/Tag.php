<?php

/**
 * Grid for tags
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Tag extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    
    /**
     * Overwrite grid constructor
     * set the controller and block
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. evozon_blog/adminhtml_tag
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_tag';
        $this->_headerText = $this->__('Blog Tags');

        parent::__construct();
        $this->_updateButton('add', 'label', Mage::helper('evozon_blog')->__('Add New Tag'));
        $this->setTemplate('evozon/blog/grid.phtml');
    }

    /**
     * Check whether it is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        if (!Mage::app()->isSingleStoreMode()) {
            return false;
        }
        return true;
    }
    
    public function getTitle()
    {
        return Mage::helper('evozon_blog')->__('Manage Tags');
    }

}
