<?php

/**
 * Blog Search Layer Model
 * Overwrites the search layer from CatalogSearch
 * Manipulates the products collection returned after the search
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Search_Layer extends Mage_CatalogSearch_Model_Layer
{

    /**
     * key by which the product collection can be found in $_searchCollection
     */
    const EVOZON_BLOG_SEARCH_PRODUCTS_TYPE = 'products';

    /**
     * key by which the posts collection can be found in $_searchCollection
     */
    const EVOZON_BLOG_SEARCH_POSTS_TYPE = 'posts';

    /**
     * @var null | array Related product Ids
     */
    protected $_relatedProductIds = null;

    /**
     * @var null | array Related product Ids
     */
    protected $_postCollection = null;

    /**
     * @var array collection of post and products grouped in an array
     */
    protected $_searchCollection = array();

    /**
     * @return array of collections
     */
    public function getSearchCollection()
    {
        if (empty($this->_searchCollection)) {
            $this->_prepareSearchCollection();
        }

        return $this->_searchCollection;
    }

    /**
     * Get current layer product collection
     *
     * @overriden
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $this->_prepareSearchCollection();
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        }

        return $collection;
    }

    /**
     * Prepare product collection 
     * If the search feature is enabled, attach the products related to the blog posts
     * 
     * @overriden
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Mage_Catalog_Model_Resource_Eav_Resource_Product_Collection $collection
     * @return \Evozon_Blog_Model_Search_Layer
     */
    public function prepareProductCollection($collection)
    {
        if (Mage::getModel('evozon_blog/search')->getConfig()->getSearchStatus()) {
            $collection->setProductIdsRelatedToPosts($this->getRelatedProductIds());
        }

        parent::prepareProductCollection($collection);

        return $this;
    }

    /**
     * Get related product Ids to Posts
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getRelatedProductIds()
    {
        if (!is_null($this->_relatedProductIds)) {
            return $this->_relatedProductIds;
        }

        $postCollection = $this->getPostCollection();
        if (empty($postCollection)) {
            return array();
        }

        $this->_relatedProductIds = Mage::getResourceModel('evozon_blog/post_relations_product')
            ->getRelatedProductsForSearch($postCollection->getAllIds());

        return $this->_relatedProductIds;
    }
    
    /**
     * Accessing post collection for the "SEARCH IN" block in the search layer
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Search
     */
    public function getPostCollection()
    {
        if (is_null($this->_postCollection)) {
            $this->_postCollection = Mage::getModel('evozon_blog/search')->getPostCollection();
            $this->setSearchCollection(self::EVOZON_BLOG_SEARCH_POSTS_TYPE, $this->_postCollection);
        }

        return $this->_postCollection;
    }

    /**
     * Creating an array that will contain both the products and the posts collection
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Search_Layer
     */
    protected function _prepareSearchCollection()
    {
        if (!isset($this->_searchCollection[self::EVOZON_BLOG_SEARCH_PRODUCTS_TYPE])) {
            $productCollection = Mage::getResourceModel('evozon_blog/search_catalog_fulltext_collection');
            $this->prepareProductCollection($productCollection);

            $this->_productCollections[$this->getCurrentCategory()->getId()] = $productCollection;
            $this->setSearchCollection(self::EVOZON_BLOG_SEARCH_PRODUCTS_TYPE, $productCollection);
        }

        return $this;
    }
    
    /**
     * Adding collections to the search collection
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param $type string key under which the collection can be retrieved
     * @param $collection collection to be added
     */
    protected function setSearchCollection($type, $collection)
    {
        if (!isset($this->_searchCollection[$type])) {
            $this->_searchCollection[$type] = $collection;
        }

        return $this;
    }

}
