<?php
/**
 * Blog Search Database Model
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Search_Engine_Mysql extends Mage_Core_Model_Abstract
{
    /**
     * Search type LIKE
     */
    const SEARCH_TYPE_LIKE              = 1;
    /**
     * Search type FULLTEXT
     */
    const SEARCH_TYPE_FULLTEXT          = 2;
    /**
     * Search type COMBINE
     */
    const SEARCH_TYPE_COMBINE           = 3;
    /**
     * XML Path to search type config
     */
    const XML_PATH_EVOZON_BLOG_SEARCH_TYPE  = 'evozon_blog_search/search_post/search_type';

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/search_indexer_mysql');
    }

    /**
     * Standard model initialization
     *
     * @param string $resourceModel
     * @return Mage_Core_Model_Abstract
     */
    protected function _init($resourceModel)
    {
        $this->_setResourceModel($resourceModel,'evozon_blog/search_collection_mysql');
    }

    /**
     * Regenerate all Stores index
     *
     * Examples:
     * (null, null) => Regenerate index for all stores
     * (1, null)    => Regenerate index for store Id=1
     * (1, 2)       => Regenerate index for post Id=2 and its store view Id=1
     * (null, 2)    => Regenerate index for all store views of post Id=2
     *
     * @param int|null $storeId Store View Id
     * @param int|array|null $postIds Post Entity Id
     *
     * @return Evozon_Blog_Model_Search_Fulltext
     */
    public function rebuildIndex($storeId = null, $postIds = null)
    {
        Mage::dispatchEvent('evozon_blog_search_index_process_start', array(
            'store_id'      => $storeId,
            'post_ids'   => $postIds
        ));

        $this->getResource()->rebuildIndex($storeId, $postIds);

        Mage::dispatchEvent('evozon_blog_search_index_process_complete', array());

        return $this;
    }

    /**
     * Delete index data
     *
     * Examples:
     * (null, null) => Clean index of all stores
     * (1, null)    => Clean index of store Id=1
     * (1, 2)       => Clean index of post Id=2 and its store view Id=1
     * (null, 2)    => Clean index of all store views of post Id=2
     *
     * @param int $storeId Store View Id
     * @param int $postId Product Entity Id
     *
     * @return Evozon_Blog_Model_Search_Fulltext
     */
    public function cleanIndex($storeId = null, $postId = null)
    {
        $this->getResource()->cleanIndex($storeId, $postId);
        return $this;
    }

    /**
     * Reset search results cache
     *
     * @return Evozon_Blog_Model_Search_Fulltext
     */
    public function resetSearchResults()
    {
        $this->getResource()->resetSearchResults();
        return $this;
    }

    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Query $query
     *
     * @return Evozon_Blog_Model_Search_Fulltext
     */
    public function prepareResult($query = null)
    {
        if (!$query instanceof Mage_CatalogSearch_Model_Query) {
            $query = Mage::helper('catalogsearch')->getQuery();
        }
        $queryText = Mage::helper('catalogsearch')->getQueryText();
        if ($query->getSynonymFor()) {
            $queryText = $query->getSynonymFor();
        }

        $postIds = $this->getResource()->prepareResult($this, $queryText, $query);
        
        return $postIds;
    }

    /**
     * Retrieve search type
     *
     * @param int $storeId
     * @return int
     */
    public function getSearchType($storeId = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_EVOZON_BLOG_SEARCH_TYPE, $storeId);
    }
}