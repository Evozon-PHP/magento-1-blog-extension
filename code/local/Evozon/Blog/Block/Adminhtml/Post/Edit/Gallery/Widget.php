<?php
/**
 * Gallery Widget popup window
 * It is displayed when clicking the "Insert Gallery" button from the post content editor
 * 
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Adding the script that creates a new object from gallery.js
     * it will send as parameters post id and store id
     * 
     * To see why we are instatiating $_blockgroup, $_controller and $_mode -> see _prepareLayout() function from parent class
     */
    public function __construct()
    {
        parent::__construct();

        $this->_blockGroup = 'evozon_blog';
        $this->_controller = 'adminhtml_post_edit';
        $this->_mode = 'gallery_widget';
        
        $this->_headerText = $this->helper('evozon_blog')->__('Gallery Insertion');

        $this->removeButton('reset');
        $this->removeButton('back');
        
        $this->_updateButton('save', 'label', $this->helper('evozon_blog')->__('Insert Gallery'));
        $this->_updateButton('save', 'class', 'add-widget');
        $this->_updateButton('save', 'id', 'insert_button');
        $this->_updateButton('save', 'onclick', 'gWidget.insertWidget()');
        
        $this->_formScripts[] = 'gWidget = new GalleryWidget.Widget('
            . '"widget_options_form", "evozon_blog/adminhtml_post_edit_gallery_images", "widget_options", "'
            . $this->getUrl('*/*/loadFields') .'", "'. $this->getRequest()->getParam('widget_target_id') . '", "' 
            . $this->getRequest()->getParam('post') . '", "' . $this->getRequest()->getParam('store') . '");';
    }
}