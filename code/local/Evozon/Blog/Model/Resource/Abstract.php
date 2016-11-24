<?php

/**
 * Evozon Blog entity abstract model, from where 
 *
 * @category    Evozon
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 */
abstract class Evozon_Blog_Model_Resource_Abstract extends Mage_Catalog_Model_Resource_Abstract
{
    /**
     * Identifuer of default store
     * used for loading default data for entity
     */
    const DEFAULT_STORE_ID = 0;

    /**
     * Store firstly set attributes to filter selected attributes when used specific store_id
     *
     * @var array
     */
    protected $_attributes = array();

    /**
     * Redeclare attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return 'evozon_blog/resource_eav_attribute';
    }

    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return self::DEFAULT_STORE_ID;
    }
}
