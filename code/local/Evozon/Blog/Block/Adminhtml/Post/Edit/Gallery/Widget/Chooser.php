<?php

/**
 * Helper block for the Post Gallery widget type
 * It will prepare the hidden field "images" and will add an afterElementHtml block with the gallery images for the current blog post
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser extends Mage_Adminhtml_Block_Template
{

    /**
     * Request parameters from widget creation
     * 
     * @var array
     */
    protected $_request = array();

    /**
     * Block construction
     * Preparing images block data (store, image values and post)
     *
     * @param array $arguments Object data
     */
    public function __construct($arguments = array())
    {
        parent::__construct($arguments);

        $paramsJson = $this->getRequest()->getParam('widget');
        if (empty($paramsJson)) {
            return $this;
        }

        $this->_request = Mage::helper('core')->jsonDecode($paramsJson);
        
        //setting store, post, images defaults
        $this->setParameter('store');
        $this->setParameter('post');
        $this->setParameter('images');
    }

    /**
     * Setting parameters values to be sent to the images rendering block
     * The paramter is either the received value, or zero, if it is the first time it`s initialized
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $name
     * @return string | int
     */
    protected function setParameter($name)
    {
        if (isset($this->_request['values'][$name])) {
            $request = $this->_request['values'][$name];
            $this->setData($name, $request);
            
            return $this;
        }

        $this->setData($name, '0');
        return $this;
    }

    /**
     * Prepare element html
     * Adding the hidden input that will keep the selected images ids
     * Adding the images block that will display the images added in the gallery for the current post 
     * 
     * @param Varien_Data_Form_Element_Abstract $element
     * @return \Varien_Data_Form_Element_Abstract
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function prepareElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $imageIdsInput = new Varien_Data_Form_Element_Hidden();
        $imageIdsInput->setForm($element->getForm())
            ->setId('gallery-images')
            ->setName($element->getName())
            ->setClass('widget-option input-text')
            ->setValue($this->getImages());

        $imagesBlock = $this->getLayout()
            ->createBlock('evozon_blog/adminhtml_post_edit_gallery_widget_chooser_images')
            ->setStore($this->getStore())
            ->setPost($this->getPost())
            ->setImages($this->getImages());

        // set the input hidden and images block to the element
        $element->setData('after_element_html', $imageIdsInput->getElementHtml() . $imagesBlock->toHtml());

        return $element;
    }

}
