<?php

/**
 * Search Engine Abstract
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Resource_Search_Engine_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Multi add entities data to search table
     *
     * @param int $storeId
     * @param array $entityIndexes
     */
    abstract function saveEntityIndexes($storeId, $entityIndexes);

    /**
     * Add entity data to index
     *
     * @param int $entityId
     * @param int $storeId
     * @param array $index
     */
    abstract function saveEntityIndex($entityId, $storeId, $index);

    /**
     * Remove entity data from fulltext search table
     *
     * @param int $storeId
     * @param int $entityId
     *
     * @return Evozon_Blog_Model_Resource_Search_Engine
     */
    abstract function cleanIndex($storeId = null, $entityId = null);

    /**
     * Retrieve fulltext search result data collection
     *
     * @return Evozon_Blog_Model_Search_Collection
     */
    abstract function getResultCollection();

    /**
     * Define if engine is available
     *
     * @return bool
     */
    abstract function test();

    public function isLayerNavigationAllowed()
    {
        return true;
    }
}