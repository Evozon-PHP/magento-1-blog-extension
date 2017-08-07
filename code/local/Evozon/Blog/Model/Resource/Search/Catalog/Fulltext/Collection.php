<?php

/**
 * Fulltext Collection manipulates the product collection returned from the catalogsearch action
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Catalog_Fulltext_Collection extends Mage_CatalogSearch_Model_Resource_Fulltext_Collection
{

    /**
     * Product ids related to posts, which are set from the search layer
     * @var array
     */
    protected $_productIdsRelatedToPosts = array();

    /**
     * Add search query filter
     * If the Blog Search feature is enabled and there are products attached to resulting post search results
     * The catalogsearch_result table has to be appended with received products from the post j
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @overriden
     * @param string $query
     * @return Evozon_Blog_Model_Resource_Search_Catalog_Fulltext_Collection
     */
    public function addSearchFilter($query)
    {
        parent::addSearchFilter($query);

        $queryId = $this->_getQuery()->getId();
        
        $productIds = $this->getProductIdsRelatedToPosts();
        if (!empty($productIds) && $queryId) {
            Mage::getResourceSingleton('evozon_blog/search_catalog_fulltext')->appendResult($queryId, $productIds);
        }

        return $this;
    }

    /**
     * Get found products ids
     * This method is used starting with Magento version 1.9.3
     * It was overwritten in order to add the blog posts related products
     * This method should not be used for Magento versions lower than 1.9.3 because it will generate fatal errors
     *
     * @return array
     */
    public function getFoundIds()
    {
        if (is_null($this->_foundData)) {
            /** @var Mage_CatalogSearch_Model_Fulltext $preparedResult */
            $preparedResult = Mage::getSingleton('catalogsearch/fulltext');
            $preparedResult->prepareResult();
            $this->_foundData = $preparedResult->getResource()->getFoundData();
            $this->appendBlogRelatedProducts();
        }
        if (isset($this->_orders[self::RELEVANCE_ORDER_NAME])) {
            $this->_resortFoundDataByRelevance();
        }

        return array_keys($this->_foundData);
    }

    /**
     * Append the blog related products to collection
     * This method is used starting with Magento version 1.9.3
     * This method should not be used for Magento versions lower than 1.9.3 because it will generate fatal errors
     */
    protected function appendBlogRelatedProducts() {
        $productIds = $this->getProductIdsRelatedToPosts();
        foreach ($productIds as $productId) {
            $this->_foundData[$productId] = 0;
        }
    }
    
    /**
     * Set products ids received from the search layer
     * 
     * @param array $productIds
     * @return Evozon_Blog_Model_Resource_Search_Catalog_Fulltext_Collection
     */
    public function setProductIdsRelatedToPosts($productIds)
    {
        $this->_productIdsRelatedToPosts = $productIds;

        return $this;
    }

    /**
     * Getting the products ids
     * 
     * @return array
     */
    public function getProductIdsRelatedToPosts()
    {
        return $this->_productIdsRelatedToPosts;
    }

}
