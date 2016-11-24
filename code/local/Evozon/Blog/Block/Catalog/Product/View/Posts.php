<?php

/**
 * Block that displays the related articles on a product page
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Catalog_Product_View_Posts extends Evozon_Blog_Block_Abstract
{

    /**
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function _construct()
    {
        if ($this->getIsEnabled()) {
            $this->setLimit($this->getConfigModel()->getPostCatalogConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_PRODUCT_PAGE_LIMIT));
        }
    }

    /**
     * Default template file
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/catalog/product/view/posts.phtml';
    }

    /**
     * Accesing the register and retrieving the current product state
     * and get the product id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int 
     */
    protected function getProductId()
    {
        $id = Mage::app()->getRequest()->getParam('id');

        if (Mage::registry('current_product')) {
            $id = Mage::registry('current_product')->getId();
        }

        return $id;
    }

    /**
     * Retrieving only the related articles objects
     * and applying filters to the posts collection (store, published date and limit from system configurations)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    public function getRelatedPosts()
    {
        $postIds = (array) Mage::getResourceModel('evozon_blog/catalog_product')
                ->getPostIds($this->getProductId());

        $collection = Mage::getModel('evozon_blog/post')
            ->getResourceCollection()
            ->addAttributeToSelect(array('title', 'small_image', 'publish_date'))
            ->addAttributeToFilter('entity_id', array('in' => $postIds))
            ->addFrontendVisibilityFilters()
            ->addRequestPaths()
            ->setOrder('publish_date', 'desc');

        if ($this->getLimit()) {
            $collection->setPageSize($this->getLimit());
        }

        return $collection;
    }

    /**
     * Getting is enabled status from config
     * 
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getDataSetDefault(
            'is_enabled', 
            $this->getConfigModel()->getPostCatalogConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_PRODUCT_PAGE_ENABLED)
        );
    }
}
