<?php

/**
 * Create the block for related products to specific article
 * This block will extend Mage_Catalog_Block_Product_List
 * And will use the package`s product listing template
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_View_Products extends Mage_Catalog_Block_Product_List
{
    /**
     * The block will be constructed only if it is enabled
     */
    public function __construct()
    {
        if ($this->getIsEnabled()) {
            return false;
        }
        parent::__construct();
    }

    /**
     * Set template
     * 
     * @return string
     */
    public function getTemplate()
    {
        if (empty($this->_template)) {
            return 'catalog/product/list.phtml';
        }

        return $this->_template;
    }

    /**
     * Set the basic conditions for the block to be displayed
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if ($this->getIsEnabled()) {
            $this->setLimit($this->getConfigModel()->getPostProductsConfig(Evozon_Blog_Model_Config_Post::RELATED_PRODUCTS_POST_PAGE_LIMIT));
        }
    }

    /**
     * The block will be displayed only if the block is enabled and there are products related to the article
     * The toolbar with filtering and pagination is disabled
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getIsEnabled() && count($this->getRelatedProducts()) > 0) {

            if ($block = $this->getChild('toolbar')) {
                $this->unsetChild('toolbar');
            }

            return '<h2>' . $this->__('Related Products') . '</h2>' . parent::_toHtml();
        }

        return '';
    }

    /**
     * Accessing the model and getting the post-product relations objects
     * And limit the nr of related products to be displayed by the data from config
     * The products should be filtered by: store, visibility,status (enabled) and stock availability
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return  \Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function getRelatedProducts()
    {
        $productsRelationsCollection = $this->getPost()->getProductsCollection();

        if ($this->getLimit()) {
            $productsRelationsCollection->setPageSize($this->getLimit());
        }

        return $productsRelationsCollection;
    }

    /**
     * Overrides the core class collection and sets the related products collection to be rendered for the viewer
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return type
     */
    public function getLoadedProductCollection()
    {
        return $this->getRelatedProducts();
    }

    /**
     * Set current view mode
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getMode()
    {
        return 'grid';
    }

    /**
     * if post model is not set, then return the post model from registry.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    protected function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post', Mage::registry('blog_post'));
        }

        return $this->getData('post');
    }

    /**
     * Getting is enabled value
     * 
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getDataSetDefault(
                'is_enabled', $this->getConfigModel()->getPostProductsConfig(Evozon_Blog_Model_Config_Post::RELATED_PRODUCTS_POST_PAGE_ENABLED)
        );
    }

    /**
     * Return the config model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Config
     */
    public function getConfigModel()
    {
        return Mage::getSingleton('evozon_blog/config');
    }
}
