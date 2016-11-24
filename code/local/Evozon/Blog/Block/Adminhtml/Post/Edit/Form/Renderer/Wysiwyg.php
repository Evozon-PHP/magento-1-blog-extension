<?php

/**
 * Editor renderer for the post_content attribute
 * It will have a custom added button on top, that will be visible always
 * The button will have the "Insert Gallery" functionality
 * Post id and store id will be sent via onclick action in order to filter the images
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Form_Renderer_Wysiwyg extends Varien_Data_Form_Element_Editor
{

    /**
     * Setting up the config for our render
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _construct()
    {
        $this->setData('config', Mage::getSingleton('cms/wysiwyg_config')->getConfig($this->getEditorConfig()));
    }

    /**
     * Setting editor configurations
     * 
     * @return array
     */
    public function getEditorConfig()
    {
        return array(
            'store_id' => $this->getData('store_id'),
            'document_base_url' => $this->getData('store_media_url'),
            'enabled' => true,
            'hidden' => false,
            'add_variables' => false,
            'use_container' => true,
            'container_class' => 'hor_scroll',
            'add_widgets' => true
        );
    }

    /**
     * Return Editor top Buttons HTML
     * This specific button will add an "Insert Gallery" button to make our cool feature more visible
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function _getButtonsHtml()
    {
        $buttonsHtml = '<div style="float:left;clear:both">' . parent::_getButtonsHtml() . '</div>';

        // Button to widget insert gallery
        $buttonsHtml .= $this->_getButtonHtml(array(
            'title' => $this->translate('Insert Gallery...'),
            'onclick' => "galleryTools.openDialog('" . $this->getWidgetWindowUrl() . "widget_target_id/"
            . $this->getHtmlId() . "/post/" . Mage::registry('evozon_blog')->getId() .  "/store/" . Mage::registry('evozon_blog')->getStoreId() . "')",
            'class' => 'add gallery plugin',
            'style'     => ''
        ));

        return $buttonsHtml;
    }
    
    /**
     * URL to widget controller that opens Gallery window
     * 
     * @return string
     */
    protected function getWidgetWindowUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/blog_widget/index');
    }

}
