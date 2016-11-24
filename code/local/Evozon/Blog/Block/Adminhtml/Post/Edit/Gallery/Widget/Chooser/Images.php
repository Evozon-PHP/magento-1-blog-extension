<?php

/**
 * Blog post gallery`s images preview
 * It will show all existing images for the current post, as well as the selected ones *
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser_Images extends Mage_Adminhtml_Block_Template
{

    /**
     * Store parameter received on block construct
     * 
     * @see Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser , function prepareElementHtml()
     * @var int
     */
    protected $_store;
    
    /**
     * Post id parameter
     * 
     * @see Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser , function prepareElementHtml()
     * @var int
     */
    protected $_post;
    
    /**
     * Selected images ids recieved via block creation/call
     * 
     * @see Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser , function prepareElementHtml()
     * @var  string
     */
    protected $_images;

    /**
     * Sets the template if is not specified
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Returns current file template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/edit/gallery/chooser/images.phtml';
    }
    
    /**
     * Retrieves all images for the current post
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getGalleryImages()
    {
        $images = $this->getPost()->getImages($this->getStore());

        return $images;
    }

    /**
     * Return the post model from registry
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    public function getPost()
    {
        return Mage::getModel('evozon_blog/post')->load($this->_post);
    }

    /**
     * Highlights previously selected images when wanting to edit the gallery widget
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string | array
     */
    public function getSelected()
    {
        $selectedImagesIds = explode(',', $this->_images);
        
        return $selectedImagesIds;
    }

    /**
     * Sets selected images from the gallery widget popup window
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $images
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser_Images
     */
    public function setImages($images)
    {
        $this->_images = $images;
        
        return $this;
    }

    /**
     * Getting store id for images to be filtered by
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int
     */
    public function getStore()
    {
        return $this->_store;
    }

    /**
     * Sets post id received via block creation/call
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $post
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser_Images
     */
    public function setPost($post)
    {
        $this->_post = $post;
        
        return $this;
    }

    /**
     * Sets store value received on block creation
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $store
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Chooser_Images
     */
    public function setStore($store)
    {
        $this->_store = $store;
        
        return $this;
    }

}
