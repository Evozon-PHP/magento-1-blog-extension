<?php

/**
 * Attribute  model for Evozon Blog
 *
 * @category    Evozon
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Eav_Attribute extends Mage_Catalog_Model_Resource_Eav_Attribute
{
    /**
     * setting scope constants
     */
    const SCOPE_STORE = 0;
    const SCOPE_GLOBAL = 1;
    const SCOPE_WEBSITE = 2;
    const MODULE_NAME = 'Evozon_Blog';
    const ENTITY = 'evozon_blog_eav_attribute';

    protected $_eventPrefix = 'evozon_blog_entity_attribute';
    protected $_eventObject = 'attribute';

    /**
     * Array with labels
     *
     * @var array
     */
    static protected $_labels = null;

    /**
     * constructor
     *
     * @access protected
     * @return void
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/attribute');
    }

    /**
     * Check is an attribute used in EAV index
     *
     * @return bool
     */
    public function isIndexable()
    {
        return false;
    }

}
