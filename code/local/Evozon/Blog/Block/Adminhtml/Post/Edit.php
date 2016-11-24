<?php

/**
 * Implement Edit Post Block
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

    /**
     * Init class & set defaults & define buttons
     * 
     * @author      Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_post';       

        // buttons: 'delete','duplicate', 'save' and 'save and continue edit'.
        $this->_updateButton('delete', 'label', $this->__('Delete Post'));
        $this->_updateButton('save', 'label', $this->__('Save Post'));
        $this->_addButton(
            'save_and_edit_button',
            array(
                'label' => $this->__('Save Post and Continue Edit'),
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
     * @author      Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getHeaderText()
    {
        if (Mage::registry('evozon_blog')->getId()) {
            return $this->__('Edit Post |  %s', $this->escapeHtml(Mage::registry('evozon_blog')->getTitle()));
        } else {
            return $this->__('New Post');
        }
    }

    /**
     * Load wysiwyg on demand and prepare layout
     * 
     * @author      Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        
        if (Mage::helper('evozon_blog')->isModuleEnabled('Mage_Cms') && Mage::getSingleton('cms/wysiwyg_config')->isEnabled()
        ) {
            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }
}
