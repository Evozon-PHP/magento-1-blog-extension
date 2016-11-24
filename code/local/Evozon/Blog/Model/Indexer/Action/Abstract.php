<?php

/**
 * Evozon Blog Indexer abstract Action class
 * Holds the flow of a reindexing, leaving up to the extending classes to overwrite the custom behaviour
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Indexer_Action_Abstract extends Mage_Core_Model_Abstract
{
    
    const EVOZON_BLOG_INDEXER_REWRITE_GENERATOR = "evozon_blog/indexer_urlRewrite_rewriteGenerator";

    /**
     * @var int | null post to be reindexed (it is null for a full reindexing)
     */
    protected $_postIds = null;

    /**
     * @var array storeIds to create rewrites for
     */
    protected $_storeId = null;

    /**
     * @var null | Evozon_Blog_Model_Indexer_UrlRewrite_RewriteGenerator
     */
    protected $_rewriteGenerator = null;

    /**
     * Initialize resources
     */
    public function _construct()
    {
        $this->_init(Evozon_Blog_Model_Factory::EVOZON_BLOG_URL_INDEXER);
        $this->setRewriteGenerator(Mage::getModel(self::EVOZON_BLOG_INDEXER_REWRITE_GENERATOR));
    }

    /**
     * General action of reindexing
     * The inside calls and flow can be changed in the extending classes
     * -creates rewrites
     * -validates rewrites
     * -saves rewrites
     */
    public function reindex()
    {
        try {
            $this->_createRewrites();
            $this->_saveRewrites();
        } catch (Evozon_Blog_Model_Exception_IndexFactory $exc) {
            Mage::logException($exc);
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Calls in for the rewrites generator in order to create
     * and validate rewrites
     * 
     * @return \Evozon_Blog_Model_Indexer_Action_Abstract
     */
    protected function _createRewrites()
    {
        $this->getRewriteGenerator()
            ->setStoreId($this->getStoreId())
            ->setPostIds($this->getPostIds())
            ->prepareRewrites()
            ->createRewrites()
            ->validateRewrites();

        return $this;
    }

    /**
     * Saving rewrites
     * @return $this
     */
    protected function _saveRewrites()
    {
        $rewrites = $this->getRewrites();
        if (!empty($rewrites)) {
            $this->getResource()->saveRewrites($rewrites);
        }

        return $this;
    }

    /**
     * Deleting rewrites is triggered by the removal of a post
     * @return \Evozon_Blog_Model_Indexer_Action_Abstract
     */
    public function deleteRewrites()
    {
        return $this;
    }
    
    /**
     * Deleting existing rewrites
     *
     * @return mixed
     */
    protected function _deleteRewrites()
    {
        try {
            $this->getResource()
                ->deleteRewrites($this->getPostIds(), $this->getStoreId());
        } catch (\Exception $e) {
            throw new Exception($e);
        }
        return $this;
    }
    
    /**
     * Getting the rewrites prepared by the context
     * The rewrites have been validated and are ready to be saved
     * 
     * @return Varien_Data_Collection
     */
    public function getRewrites()
    {
        return $this->getRewriteGenerator()->getRewritesToSave();
    }

    /**
     * The rewrites generator will be responsible for creating the rewrites according to the edition
     *
     * @param $generator
     * @return Evozon_Blog_Model_Indexer_Action_Abstract
     */
    public function setRewriteGenerator($generator)
    {
        if (!$generator instanceof Evozon_Blog_Model_Indexer_UrlRewrite_Interface)
        {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10601);
        }

        $this->_rewriteGenerator = $generator;
        return $this;
    }

    public function getRewriteGenerator()
    {
        if (is_null($this->_rewriteGenerator))
        {
            $this->_rewriteGenerator = Mage::getModel(self::EVOZON_BLOG_INDEXER_REWRITE_GENERATOR);
        }

        return $this->_rewriteGenerator;
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
}