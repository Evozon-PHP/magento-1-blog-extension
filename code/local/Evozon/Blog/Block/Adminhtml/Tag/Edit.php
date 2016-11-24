<?php

/**
 * Edit tag block
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Tag_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class & set defaults & define buttons
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {  
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_tag';
     
        parent::__construct();
        
        // buttons: 'save', 'delete' and 'save and continue edit'.
        $this->_updateButton('save', 'label', $this->__('Save Tag'));
        $this->_updateButton('delete', 'label', $this->__('Delete Tag'));
    }  
     
    /**
     * Get Header text
     *
     * @return string
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getHeaderText()
    {  
        if (Mage::registry('evozon_blog_tag')->getId()) {
            return $this->__('Edit Tag');
        }  
        else {
            return $this->__('New Tag');
        }  
    }
}