<?php

/**
 * Implement Edit Comment Block
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class & set defaults & define buttons
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {  
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_comment';
     
        parent::__construct();
     
        $this->_updateButton('save', 'label', $this->__('Save Comment'));
        $this->_updateButton('delete', 'label', $this->__('Delete Comment'));
        $this->_addButton(
            'save_and_edit_button', 
            array(
                'label' => $this->__('Save Comment and Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class' => 'save'
            ), 
            100
        );
        
        // add save and continue javascript code
        $this->_formScripts[] = "function saveAndContinueEdit() {                
            var activeTab = $$('a.tab-item-link.active')[0];
            editForm.submit($('edit_form').action+'back/edit/active_tab/'+activeTab.name+'/');       
        }";
    }  
     
    /**
     * Get Header text
     *
     * @return string
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getHeaderText()
    {  
        if (Mage::registry('evozon_blog_comment')->getId()) {
            return $this->__('Edit Comment');
        }
    } 
}
