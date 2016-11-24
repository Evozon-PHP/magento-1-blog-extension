<?php

/**
 * Blog Search Model
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Search extends Mage_Core_Model_Abstract
{

    /**
     * Post Collection
     *
     * @var
     */
    protected $_postCollection = null;

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/search');
    }

    /**
     * Get selected search instance
     *
     * @return false | Mage_Core_Model_Abstract
     */
    public function getSearchModelInstance()
    {
        $engineBlock = $this->getConfig()->getCurrentEngineBlock();
        $search = Mage::getModel($engineBlock['engine']['model']);

        return $search;
    }

    /**
     * Get current layer post collection
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    public function getPostCollection()
    {
        if (is_null($this->_postCollection)) {
            $searchInstance = $this->getSearchModelInstance();
            $collection = $searchInstance->getCollection();
            $this->preparePostCollection($collection);
            
            $this->_postCollection = $collection;
        }

        return $this->_postCollection;
    }

    /**
     * Prepare post collection
     *
     * @param Evozon_Blog_Model_Resource_Search_Collection_Database $collection
     * @return Mage_Catalog_Model_Layer
     */
    public function preparePostCollection($collection)
    {
        $collection
            ->addAttributeToSelect('*')
            ->addSearchFilter(Mage::helper('catalogsearch')->getQuery()->getQueryText())
            ->setStore(Mage::app()->getStore());

        return $collection;
    }
    
    /**
     * Accessing search config params
     * 
     * @return Evozon_Blog_Model_Search_Config
     */
    public function getConfig()
    {
        return Mage::getModel('evozon_blog/search_config');
    }

}
