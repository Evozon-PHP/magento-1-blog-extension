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
     * @param type $query
     */
    public function addSearchFilter($query)
    {
        Mage::getSingleton('catalogsearch/fulltext')->prepareResult();
        
        $queryId = $this->_getQuery()->getId();
        
        $productIds = $this->getProductIdsRelatedToPosts();
        if (!empty($productIds)) {
            Mage::getResourceSingleton('evozon_blog/search_catalog_fulltext')->appendResult($queryId, $productIds);
        }

        $this->getSelect()->joinInner(
            array('search_result' => $this->getTable('catalogsearch/result')),
            $this->getConnection()->quoteInto(
                'search_result.product_id=e.entity_id AND search_result.query_id=?', $queryId
            ),
            array('relevance' => 'relevance')
        );
        
        return $this;
    }
    
    /**
     * Set products ids received from the search layer
     * 
     * @param type $productIds
     */
    public function setProductIdsRelatedToPosts($productIds)
    {
        $this->_productIdsRelatedToPosts = $productIds;

        return $this;
    }

    /**
     * Getting the products ids
     * 
     * @return type
     */
    public function getProductIdsRelatedToPosts()
    {
        return $this->_productIdsRelatedToPosts;
    }

}
