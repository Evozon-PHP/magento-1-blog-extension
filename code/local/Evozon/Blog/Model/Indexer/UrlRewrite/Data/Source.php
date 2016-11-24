<?php

/**
 * Data source class used to prepare raw data from which the url key will be created
 * in order to prepare the new url rewrites
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_Data_Source
{
    /**
     * data source collection class name
     * @var string
     */
    protected $_resourceCollectionName = 'evozon_blog/indexer_urlRewrite_data_source_collection';

    /**
     * data source resource class name
     * @var string
     */
    protected $_resourceName = 'evozon_blog/indexer_urlRewrite_data_source';

    /**
     * post ids that require for the attributes to be fetched
     * @var array | null
     */
    protected $_postIds = array();
    protected $_storeIds = array();

    /**
     * Evozon_Blog_Model_Indexer_Urlrewrite_DataSource constructor.
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        if (isset($args['store_ids']))
        {
            $this->setStoreIds($args['store_ids']);
        }

        if (isset($args['post_ids']))
        {
            $this->setPostIds($args['post_ids']);
        }
    }

    /**
     * Creating a collection by merging existing rewrites collection and the template one
     * The join will happen only if in the dummy collection already exists an object with the same id as the ones from the existing rewrites
     * Additional fields will be added (such as target_path and url_rewrite_id), which will make sure that the data for those rewrites will be updated
     */
    public function getCollection()
    {
        $existingRewritesCollection = $this->_getExistingRewritesCollection();
        $sourceCollection = $this->_getSourceCollection();

        if (!$existingRewritesCollection->count())
        {
            return $sourceCollection;
        }

        foreach ($sourceCollection->getItems() as $item)
        {
            $rewrite = $existingRewritesCollection->getItemByColumnValue('id', $item->getId());
            if ($rewrite) {
                $item->addData($rewrite->getData());
            }
        }

        return $sourceCollection;
    }

    /**
     * Collection of template objects  that will have data required to recreate rewrites
     * (data such as attribute values, entity id and store id)
     *
     * @return Evozon_Blog_Model_Resource_Indexer_Urlrewrite_Data_Source_Collection
     */
    protected function _getSourceCollection()
    {
        $collection =  Mage::getResourceModel($this->_resourceCollectionName)
            ->setPostIds($this->getPostIds())
            ->setStoreIds($this->getStoreIds())
            ->getCollection();

        foreach ($this->getAttributesValue() as $attrCode=>$attrData) {
            $collection->addAttribute($attrCode, $attrData);
        }

        return $collection;
    }

    /**
     * Returns the collection of already existing rewrites
     * fetched with special created fields
     * in order to be able to join it with the source collection to prepare the rewrites
     *
     * @return Varien_Data_Collection
     */
    protected function _getExistingRewritesCollection()
    {
        $collection = Mage::getSingleton('evozon_blog/factory')->getRewriteCollection()
            ->loadExistingRewrites()
            ->addEntityIdFilter($this->_postIds)
            ->addStoreFilter($this->_storeIds);

        return $collection;
    }

    /**
     * Accessing resource to get attribute data for each requested entity and store
     *
     * @return array
     */
    protected function getAttributesValue()
    {
        $data = Mage::getResourceSingleton($this->_resourceName)
            ->setPostIds($this->getPostIds())
            ->setStoreIds($this->getStoreIds())
            ->getAttributesValue();

        return $data;
    }

    /**
     * In case of a full reindexing, all post ids have to be taken into account
     *
     * @return array
     */
    public function getPostIds()
    {
        if (empty($this->_postIds))
        {
            $this->_postIds = Mage::getResourceModel($this->_resourceName)
                ->getAllPostIds();
        }

        return $this->_postIds;
    }

    /**
     * If the stores were not set, then the reindexing will happen for all of the existing stores
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (empty($this->_storeIds))
        {
            $storeIds = Mage::app()->getStores();
            array_walk($storeIds, function (&$store) {
                $store = $store instanceof Mage_Core_Model_Store ? $store->getId() : -1;
            });

            $this->_storeIds = $storeIds;
        }

        return $this->_storeIds;
    }

    /**
     * When the indexer is called from a mass action - the postids are arrays
     * When the indexer is called from full reindex - the postids is null
     * When it is an individual post - the postids has an id
     *
     * @param null| array| int $postIds
     * @return null| array $this
     */
    public function setPostIds($postIds)
    {
        $this->_postIds = (is_null($postIds) || is_array($postIds)) ? $postIds : array($postIds);
        return $this;
    }

    public function setStoreIds($storeId)
    {
        $this->_storeIds = (is_null($storeId) || !$storeId) ? null : array($storeId);
        return $this;
    }
}