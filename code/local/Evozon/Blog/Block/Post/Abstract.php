<?php

/**
 * Abstract class for post block. List and view should implement this class.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Block_Post_Abstract extends Evozon_Blog_Block_Abstract
{

    /**
     * Post collection
     * 
     * @var null|Evozon_Blog_Module_Resource_Post_Collection
     */
    protected $_postCollection = null;

    /**
     * Helper to be used in block and templates
     *
     * @var \Evozon_Blog_Helper_Data 
     */
    protected $_helper;

    /**
     * Used to check if the curent page we`re on acts as a blog'
     * 
     * @var bool
     */
    protected $_isBlog = false;

    /**
     * Default constructor: set defaults;
     */
    public function __construct()
    {
        parent::__construct();
        $this->setHelper(Mage::helper('evozon_blog'));
        $this->_isBlog = Mage::registry('is_blog');

        // set the page category type (if we are not on a Evozon_Blog_PostController action)
        if (!Mage::registry('is_blog')) {
            $this->setIsBlogCategory();
        }
    }

    /**
     * Add breadcrumbs: home
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Block_Post_Abstract
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home', array(
                'label' => Mage::helper('evozon_blog')->__('Home'),
                'title' => Mage::helper('evozon_blog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));
        }

        parent::_prepareLayout();

        return $this;
    }

    /**
     * Get the store selected by user.
     * 
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * Get current post collection filtred by current store
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Module_Resource_Post_Collection
     */
    protected function _getPostCollection()
    {
        if ($this->_postCollection === null) {
            $postCollection = Mage::getModel('evozon_blog/post')
                ->getCollectionForListing();

            $this->_postCollection = $postCollection;
        }

        return $this->_postCollection;
    }

    /**
     * Use the module helper
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return type
     */
    protected function setHelper($helper)
    {
        $this->_helper = $helper;
        return $this;
    }

    /**
     * Checking if the page we`re on is setted to act as a blog page.
     * According to this value, we`ll have different filters for our blog collection
     * And also for the widget/block settings from system.xml/widget.xml
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    protected function setIsBlogCategory()
    {
        if (
            ($category = Mage::registry('current_category')) &&
            $category instanceof Mage_Catalog_Model_Category
        ) {
            $this->_isBlog = (bool) $category->getIsBlogCategory();
        }

        return $this;
    }

    /**
     * Checking if the block/widget instance can be shown 
     * The widget won`t be displayed on a category of type blog
     * And the block won`t be displayed on a simple category
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return bool
     */
    public function canShowBlock()
    {
        $canShow = false;

        if ($this->_isBlog XOR $this->getIsWidget()) {
            $canShow = true;
        }

        return $canShow;
    }

    /**
     * If post model is not set, then return the post model from registry.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    protected function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post', Mage::registry('blog_post'));
        }

        return $this->getData('post');
    }

    /*
     * Returns image helper
     * It is used where the images are needed to be displayed
     */
    public function getImageHelper()
    {
        return Mage::helper('evozon_blog/post_image');
    }

}
