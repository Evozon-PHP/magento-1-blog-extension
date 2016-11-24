<?php
/**
 * Blog Search Collection Abstract
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Collection_Abstract extends Varien_Data_Collection_Db
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_post';

    /**
     * Collection constructor
     *
     * @param Evozon_Blog_Model_Resource_Search_Collection_Abstract $resource
     */
    public function __construct($resource = null)
    {
        $this->_construct();
        $this->setConnection($this->getEntity()->getReadConnection());
        $this->_prepareStaticFields();
        $this->_initSelect();
    }

    /**
     * Init collection of post objects
     */
    protected function _construct()
    {

    }

    /**
     * Standard resource collection initalization
     *
     * @param string $model
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _init($model, $entityModel = null)
    {
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName($model));
        if ($entityModel === null) {
            $entityModel = $model;
        }
        $entity = Mage::getResourceSingleton($entityModel);
        $this->setEntity($entity);

        return $this;
    }


    /**
     * Load collection data into object items
     *
     * @return Evozon_Blog_Model_Resource_Search_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        Varien_Profiler::start('__EAV_COLLECTION_BEFORE_LOAD__');
        Mage::dispatchEvent('eav_collection_abstract_load_before', array('collection' => $this));
        $this->_beforeLoad();
        Varien_Profiler::stop('__EAV_COLLECTION_BEFORE_LOAD__');

        $this->_renderFilters();
        $this->_renderOrders();

        Varien_Profiler::start('__EAV_COLLECTION_LOAD_ENT__');
        $this->_loadEntities($printQuery, $logQuery);
        Varien_Profiler::stop('__EAV_COLLECTION_LOAD_ENT__');
        Varien_Profiler::start('__EAV_COLLECTION_LOAD_ATTR__');
        $this->_loadAttributes($printQuery, $logQuery);
        Varien_Profiler::stop('__EAV_COLLECTION_LOAD_ATTR__');

        Varien_Profiler::start('__EAV_COLLECTION_ORIG_DATA__');
        foreach ($this->_items as $item) {
            $item->setOrigData();
        }
        Varien_Profiler::stop('__EAV_COLLECTION_ORIG_DATA__');

        $this->_setIsLoaded();
        Varien_Profiler::start('__EAV_COLLECTION_AFTER_LOAD__');
        $this->_afterLoad();
        Varien_Profiler::stop('__EAV_COLLECTION_AFTER_LOAD__');
        return $this;
    }

    /**
     * Clone and reset collection
     *
     * @return Evozon_Blog_Model_Resource_Search_Collection_Abstract
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        return $idsSelect;
    }


}