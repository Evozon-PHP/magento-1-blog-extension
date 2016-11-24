<?php

/**
 * Tag unique validation that interacts with the database
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @see        Evozon_Blog_Model_Tag_Validate_Unique
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Tag_Validate_Unique extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * read connection to the resource
     * @var \Varien_Db_Adapter_Interface
     */
    protected $_adapter;

    /*
     * Construct
     */
    protected function _construct()
    {
        $this->_adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Fetching data from the name attribute table that matches the user input
     * It is used in the model to create NOT UNIQUE ON THE STORE exception
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $data presented as array(id => value, store_0 => value, store_1 => value,..)
     * @param int | null $id
     * @return array
     */
    public function validate($data, $id = null)
    {
        $adapter = $this->_adapter;

        $attr = Mage::getModel('eav/config')->getAttribute(Evozon_Blog_Model_Resource_Tag::ENTITY, 'name');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackendTable();

        $conditions = array();
        foreach ($data as $storeId => $value) {
            $conditions[] = $adapter->quoteInto("( name.store_id = ?", $storeId) . ' AND ' . $adapter->quoteInto(" name.value = ?)", $value);
        }

        $select = $adapter->select()
            ->from(array('name' => $attrTable), array('store_id', 'value'))
            ->where('attribute_id = ?', $attrId)
            ->where(new Zend_Db_Expr(implode(' OR ', $conditions)));

        if (!empty($id)) {
            $select->where('entity_id <> ?', (int) $id);
        }

        return $adapter->fetchAll($select);
    }

}
