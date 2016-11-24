<?php

/**
 * Blog Database Engine resource model
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Engine_Mysql extends Evozon_Blog_Model_Resource_Search_Engine_Abstract
{

    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/search_fulltext', 'post_id');
    }

    /**
     * Add entity data to fulltext search table
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     * @return Evozon_Blog_Model_Resource_Search_Engine_Fulltext
     */
    public function saveEntityIndex($entityId, $storeId, $index)
    {
        $this->_getWriteAdapter()->insert($this->getMainTable(), array(
            'post_id' => (int) $entityId,
            'store_id' => (int) $storeId,
            'data_index' => $index
        ));
        return $this;
    }

    /**
     * Multi add entities data to fulltext search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @return Evozon_Blog_Model_Resource_Search_Engine_Fulltext
     */
    public function saveEntityIndexes($storeId, $entityIndexes)
    {
        $data = array();
        foreach ($entityIndexes as $entityId => $index) {
            $data[] = array(
                'post_id' => (int) $entityId,
                'store_id' => (int) $storeId,
                'data_index' => $index
            );
        }

        if ($data) {
            Mage::getResourceHelper('catalogsearch')
                ->insertOnDuplicate($this->getMainTable(), $data, array('data_index'));
        }

        return $this;
    }

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility()
    {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
    }

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return false;
    }

    /**
     * Remove entity data from fulltext search table
     *
     * @param int $storeId
     * @param int $entityId
     *
     * @return Evozon_Blog_Model_Resource_Search_Engine_Fulltext
     */
    public function cleanIndex($storeId = null, $entityId = null)
    {
        $where = array();

        if (!is_null($storeId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('store_id=?', $storeId);
        }
        if (!is_null($entityId)) {
            $where[] = $this->_getWriteAdapter()->quoteInto('post_id IN (?)', $entityId);
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);

        return $this;
    }

    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return Mage::helper('catalogsearch')->prepareIndexdata($index, $separator);
    }

    /**
     * Retrieve fulltext search result data collection
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    public function getResultCollection()
    {
        $engineBlock = Mage::getModel('evozon_blog/search')->getConfig()->getCurrentEngineBlock();

        return Mage::getResourceModel($engineBlock['collection']['resource']);
    }

    /**
     * Define if engine is available
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }

}
