<?php

/**
 * The rewrite generator it is used by the indexer context
 * in order to create rewrites according to context`s required fields
 * 
 * 1. calls in for the data to recreate url paths for each post
 * 2. sets paths and data
 * 3. recreates a collection of rewrites
 * 4. validates the given collection of rewrites and changes the paths in case of duplicates
 * 5. sets the rewrites prepared to be saved
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_RewriteGenerator implements Evozon_Blog_Model_Indexer_UrlRewrite_Interface
{
     /**
     * @var null | array posts to be created rewrites for (it is null for a full reindexing)
     */
    protected $_postIds = null;

    /**
     * @var array storeIds to create rewrites for
     */
    protected $_storeId = null;
    
    /**
     * context that will prepare the rewrites
     * @var null | Evozon_Blog_Model_Indexer_Context_Abstract
     */
    protected $_context = null;
    
    /**
     * @var Varien_Data_Collection
     */
    protected $_rewrites = null;

    /**
     * Uses a rewrite generator that will incapsulate the behaviour
     * Gets the empty collection
     * Gets the existing collection
     * Merges collections
     * Sets Rewrites
     */
    public function prepareRewrites()
    {
        $collection = Mage::getModel('evozon_blog/indexer_urlRewrite_data_source')
            ->setPostIds($this->getPostIds())
            ->setStoreIds($this->getStoreId())
            ->getCollection();

        $this->setRewrites($collection);
        return $this;
    }

    /**
     * Updates dummy rewrites created above by setting the data as required by the context
     *
     * @return $this
     */
    public function createRewrites()
    {
        $collection = $this->getRewrites();
        $context = $this->getContext();

        foreach ($collection->getItems() as $post)
        {
            $data = $context->prepareRewriteData($this->_createPaths($post));
            $post->setData($data);
        }

        $this->setRewrites($collection);
        return $this;
    }

    /**
     * Create rewrite paths (id path, target path, request path)
     *
     * @param Varien_Object $post
     * @return Varien_Object
     */
    protected function _createPaths(Varien_Object $post)
    {
        return $this->getPathGenerator($post)->getPaths();
    }
    
    /**
     * Validate rewrites by using the filters set/requested by the context
     */
    public function validateRewrites()
    {
        $rewrites = $this->getRewrites();
        $this->getFilter()->checkAndResolve($rewrites);

        $this->setRewrites($rewrites->toArray($this->getRequiredFields()));
        return $this;
    }

    /**
     * Getting filter with scanners for the context
     * @return mixed
     */
    public function getFilter()
    {
        return $this->getContext()->getUniqueFilter();
    }

    /**
     * Getting required fields for specific context to format the rewrites to save
     * @return array
     */
    public function getRequiredFields()
    {
        return $this->getContext()->getRewriteRequiredFields();
    }

    /**
     * Path generator is a class responsible for generating the request path
     * based on the given data
     * 
     * @param Varien_Object $object
     * @return \Evozon_Blog_Model_Indexer_UrlRewrite_RewriteGenerator
     */
    public function getPathGenerator(Varien_Object $object)
    {
        return Mage::getModel('evozon_blog/indexer_urlRewrite_pathGenerator', $object);
    }

    /**
     * Preparing rewrites required to be saved
     *
     * @return array
     */
    public function getRewritesToSave()
    {
        $rewrites = $this->getRewrites();
        return isset($rewrites['items']) ? $rewrites['items'] : array();
    }

    /**
     * The context is responsible for creating the rewrites according to the edition
     *
     * @return Evozon_Blog_Model_Indexer_Context_Interface
     */
    public function getContext()
    {
        if (is_null($this->_context))
        {
            $this->_context = Mage::getSingleton('evozon_blog/factory')->getContextInstance();
        }

        return $this->_context;
    }
    
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }

    public function setPostIds($postIds)
    {
        $this->_postIds = $postIds;
        return $this;
    }

    public function getPostIds()
    {
        return $this->_postIds;
    }
    
     /**
     * Getting rewrites
     * @return array
     */
    public function getRewrites()
    {
        return $this->_rewrites;
    }

    /**
     * Setting rewrites
     * @return array
     */
    public function setRewrites($collection)
    {
        $this->_rewrites = $collection;
        return $this;
    }

    /**
     * @param Evozon_Blog_Model_Indexer_Context_Interface $context
     * @return \Evozon_Blog_Model_Indexer_Action_Abstract
     */
    public function setContext(Evozon_Blog_Model_Indexer_Context_Interface $context)
    {
        $this->_context = $context;
        return $this;
    }

}