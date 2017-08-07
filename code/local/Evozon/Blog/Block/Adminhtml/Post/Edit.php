<?php

/**
 * Implement Edit Post Block
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Main constructor
     * 
     * @author      Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->preparePostButtons();
    }

    /**
     * Set defaults and add or remove post grid buttons depending on conditions
     */
    public function preparePostButtons()
    {
        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_post';

        // buttons: 'delete','duplicate', 'save' and 'save and continue edit'.
        $this->_updateButton('delete', 'label', $this->__('Delete Post'));
        $this->_updateButton('save', 'label', $this->__('Save Post'));
        $this->_addButton(
            'post_prev', array(
                'label' => $this->__('Preview'),
                'class' => 'save',
                'onclick' => "window.open('{$this->createPreviewUrl()}', '_blank')",
            )
        );
        $this->_addButton(
            'save_and_edit_button', array(
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

        if (!Mage::registry('evozon_blog')->getId()) {
            $this->removeButton('post_prev');
        }
    }

    /**
     * Generate url for preview post
     * 
     * @return string
     */
    public function createPreviewUrl()
    {
        if (Mage::registry('evozon_blog')->getId()) {
            $post = Mage::registry('evozon_blog');
            $postPreviewKey =
                Mage::getModel('evozon_blog/post_preview',$post->getId())
                    ->getPreviewKey();

            return $this->getUrl(
                'blog/post/preview',
                array('id' => $post->getId(), 'previewKey' => $postPreviewKey)
            );
        }
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
