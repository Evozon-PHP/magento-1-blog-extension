<?php
/**
 * Evozon Blog Indexer Url class
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Search_Config extends Varien_Simplexml_Config
{
    /**
     * Search configuration file
     */
    const CONFIG_FILE = 'search.xml';
    /**
     * XML Config path for engine configuration
     */
    const XML_PATH_BLOG_SEARCH_ENGINE = 'evozon_blog_search/post_search/engine';

    /**
     * XML Config path for search status
     */
    const XML_PATH_BLOG_SEARCH_STATUS = 'evozon_blog_search/post_search/search_status';

    /**
     * XML Config path for search type
     */
    const XML_PATH_BLOG_SEARCH_TYPE = 'evozon_blog_search/post_search/search_type';

    /**
     * Search engine
     *
     * @var engine
     */
    protected $_engine;

    /**
     * Search type
     *
     * @var
     */
    protected $_searchType;

    /**
     * Blog search status
     *
     * @var
     */
    protected $_searchStatus;

    /**
     * Constructor
     *
     * @param null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $this->setXml(
            Mage::getConfig()
                ->loadModulesConfiguration(self::CONFIG_FILE)
                ->getNode()
        );
    }

    /**
     * Get current search engine resource model
     *
     * @return object
     */
    public function getEngine()
    {
        if (!$this->_engine) {
            $engine = Mage::getStoreConfig(self::XML_PATH_BLOG_SEARCH_ENGINE);

            /**
             * This needed if there already was saved in configuration some none-default engine
             * and module of that engine was disabled after that.
             * Problem is in this engine in database configuration still set.
             */
            if ($engine && Mage::getConfig()->getResourceModelClassName($engine)) {
                $model = Mage::getResourceSingleton($engine);
                if ($model && $model->test()) {
                    $this->_engine = $model;
                }
            }

            if (!$this->_engine) {
                $this->_engine = Mage::getResourceSingleton('evozon_blog/search_engine_mysql');
            }
        }

        return $this->_engine;
    }

    /**
     * Retrieve search type
     *
     * @return mixed
     */
    public function getSearchType()
    {
        if (!$this->_searchType) {
            $this->_searchType = Mage::getStoreConfig(self::XML_PATH_BLOG_SEARCH_TYPE);
        }

        return $this->_searchType;
    }

    /**
     * Retrieve search status
     *
     * @return mixed
     */
    public function getSearchStatus()
    {
        if (!$this->_searchStatus) {
            $this->_searchStatus = Mage::getStoreConfig(self::XML_PATH_BLOG_SEARCH_STATUS);
        }

        return $this->_searchStatus;
    }

    /**
     * Get engines block from config file
     *
     * @return array|string
     * @throws Mage_Core_Exception
     */
    public function getEngines()
    {
        $node = $this->getNode('search/engines');
        if (!$node) {
            Mage::throwException(sprintf('Missing engines node'));
        }

        return $node->asArray();
    }

    /**
     * Get engine config by name
     *
     * @param $engine
     * @return array|string
     * @throws Mage_Core_Exception
     */
    public function getEngineByName($engine)
    {
        $node = $this->getNode('search/engines' . '/' . $engine);
        if (!$node) {
            Mage::throwException(sprintf('Missing node name %s in config file.', $engine));
        }

        return $node->asArray();
    }

    /**
     * Get current engine block from config
     *
     * @param null $engineModel
     * @return bool
     */
    public function getCurrentEngineBlock($engineModel = null)
    {
        if (!$engineModel) {
            $engineModel = Mage::getStoreConfig(self::XML_PATH_BLOG_SEARCH_ENGINE);
        }

        $engines = $this->getEngines();
        $current = false;

        foreach ($engines as $engine) {
            if ($engine['engine']['resource'] == $engineModel) {
                $current = $engine;
            }
        }

        return $current;
    }
}