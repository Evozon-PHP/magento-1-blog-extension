<?php

/**
 * Grid Block for EAV posts entities
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('evozon/blog/grid.phtml');
    }
    
    /**
     * Overwrite grid constructor   
     * set the controller and block
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _prepareLayout()
    {
        // The blockGroup must match the first half of how we call the block, and controller matches the second half
        // ie. evozon_blog/adminhtml_post
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_post';
        $this->_headerText = $this->__('Blog Posts');

        $this->_updateButton('add', 'label', Mage::helper('evozon_blog')->__('Add New Post'));
        return parent::_prepareLayout();
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
        return Mage::helper('evozon_blog')->__('Manage Blog Posts');
    }

}
