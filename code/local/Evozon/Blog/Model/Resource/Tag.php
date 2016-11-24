<?php

/**
 * Resource Model for Tag Model
 *
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Tag extends Mage_Eav_Model_Entity_Abstract
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_tags';

    /**
     * @var array
     */
    protected static $_defaultAttributes = array(
        'entity_id',
        'entity_type_id',
        'attribute_set_id',
        'created_at'
    );

    /**
     * Contains all relevant entity attributes
     * 
     * @var array
     */
    protected $_entityAttributes = array(
        'name',
        'count',
        'url_key'
    );

    /**
     * Holds each attribute definition (table and id)
     * @var array
     */
    protected $_attrDefinitions = array();

    /**
     * Keeps current editable tag
     * @var Varien_Object
     */
    protected $_tag;

    /**
     * initialize resource: set main table and identifier
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType(Evozon_Blog_Model_Tag::ENTITY);
        $this->setConnection('evozon_blog_read', 'evozon_blog_write');
    }

    /**
     * Initializes data that will be used [tables and type definitions]
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Tag
     */
    protected function getAttrDefinitions()
    {
        if (empty($this->_attrDefinitions)) {
            $config = Mage::getModel('eav/config');

            foreach ($this->getEntityAttributes() as $attrCode) {
                $attr = $config->getAttribute(self::ENTITY, $attrCode);

                $this->_attrDefinitions[$attrCode] = array();
                $this->_attrDefinitions[$attrCode]['id'] = $attr->getAttributeId();
                $this->_attrDefinitions[$attrCode]['table'] = $attr->getBackendTable();
            }
        }

        return $this;
    }

    /**
     * Accesses storage array and returns table name
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $attrCode
     * @return string
     */
    protected function getAttrTable($attrCode)
    {
        return $this->_attrDefinitions[$attrCode]['table'];
    }

    /**
     * Accesses storage array and returns attribute id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $attrCode
     * @return string
     */
    protected function getAttrId($attrCode)
    {
        return $this->_attrDefinitions[$attrCode]['id'];
    }

    /**
     * Default tag attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return self::$_defaultAttributes;
    }

    /**
     * @return array
     */
    protected function getEntityAttributes()
    {
        return $this->_entityAttributes;
    }

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
     * Wrapper for main table getter
     *
     * @access public
     * @return string
     */
    public function getMainTable()
    {
        return $this->getEntityTable();
    }
    
    /**
     * It used to be able to edit the tag and see all it`s attributes for all the stores
     * 
     * Return all attribute values as array in form:
     * array(
     *   [store_id_0] => array(
     *          [attribute_code] => store_0_value,
     *          [attribute_code] => store_0_value
     *          ),
     *  [store_id_1] => array(
     *          [attribute_code] => store_1_value,
     *          [attribute_code] => store_1_value
     *          ),
     *  ....
     * )
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag $tag
     * @return array
     */
    public function getAllAttributeValues(Evozon_Blog_Model_Tag $tag)
    {
        $values = array();
        $this->getAttrDefinitions();

        foreach ($this->_attrDefinitions as $attrCode => $data) {
            $select = $this->_getReadAdapter()->select()
                ->from($data['table'], array('store_id', 'value'))
                ->where('attribute_id = ?', (int) $data['id'])
                ->where('entity_id = ?', (int) $tag->getId());

            $data = $this->_getReadAdapter()->fetchAll($select);

            foreach ($data as $row) {
                $values[$row['store_id']][$attrCode] = $row['value'];
            }
        }

        return $values;
    }

    /**
     * Get existing attribute values for specific tag object
     * For ex: it is used in order to get old attribute values on all the stores,
     * in order to know which ones to delete, update or are new
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag $tag
     * @param string $attrCode ex: name, url_key, count, etc
     * @return array
     */
    protected function _getAttributeValues($attrCode)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getAttrTable($attrCode), array('store_id', 'value'))
            ->where('attribute_id = ?', (int) $this->getAttrId($attrCode))
            ->where('entity_id = ?', (int) $this->getTag()->getId())
            ->where('store_id <> 0');

        return $select;
    }

    /**
     * Fetching rows from a select and setting data as array(store_id_0=>value, store_id_1=>value);
     * 
     * @param Evozon_Blog_Model_Tag $tag
     * @param string $attrCode
     * @return array
     */
    protected function _getExistingValuesArray($attrCode)
    {
        $select = $this->_getAttributeValues($attrCode);
        $data = $this->_getReadAdapter()->fetchAll($select);

        $names = array();
        foreach ($data as $key => $values) {
            $names[$values['store_id']] = $values['value'];
        }

        return $names;
    }

    /**
     * Since we had to save the tag on all the stores on the same time,
     * In afterSave those values will be added according to attribute and specific table
     * For all the stores (except default ones, which are added during save action)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Varien_Object $tag
     * @return Evozon_Blog_Model_Resource_Tag
     */
    protected function _afterSave(Varien_Object $tag)
    {
        parent::_afterSave($tag);
        
        $this->setTag($tag);
        $this->_saveStoreAttribute();
    }

    /**
     * Set tag that will be used for further processing
     * This way it won`t be sent as a parameter to all the data processing functions
     * 
     * @param Varien_Object $tag
     * @return \Evozon_Blog_Model_Resource_Tag
     */
    public function setTag($tag)
    {
        $this->_tag = $tag;
        return $this;
    }

    /**
     * Get tag
     * 
     * @return Varien_Object
     */
    public function getTag()
    {
        return $this->_tag;
    }

    /**
     * Checks if there are other store values
     * And performs attribute save
     * Only the data that has been left blank will be deleted
     * Only the data that has been modified/new will be insertedOnDuplicate
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag $tag
     * @return \Evozon_Blog_Model_Resource_Tag this
     */
    protected function _saveStoreAttribute()
    {
        $tag = $this->getTag();
        if (!$tag->hasInputData()) {
            return $this;
        }

        $this->getAttrDefinitions();

        $oldValues = $this->_getExistingValuesArray('name');
        $values = $tag->getInputData();

        $update = array_diff($values, array_intersect_assoc($values, $oldValues));
        $delete = array_diff($oldValues, array_intersect_key($oldValues, $values));

        if (!empty($delete)) {
            $this->_removeAttributeValues($delete);
        }

        if (!empty($values)) {
            $insertData = $this->_prepareTagDataForInsert($update);
            $this->_addAttributeValues($insertData);
            $this->_addDefaultCountOnNewObject();
        }

        return $this;
    }

    /**
     * Removes ALL attributes from specific store
     * This way we edit a tag on multiple stores at the same time
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag $tag
     * @param array $delete 
     * @return \Evozon_Blog_Model_Resource_Tag this
     */
    protected function _removeAttributeValues(array $delete)
    {
        $adapter = $this->_getWriteAdapter();
        if (empty($delete)) {
            return $this;
        }

        $id = $this->getTag()->getId();
        try {
            foreach ($delete as $storeId => $deleteValue) {
                $where[] = $adapter->quoteInto("( store_id = ?", $storeId) . 
                    ' AND ' . $adapter->quoteInto("entity_id = ?)", $id);
            }

            $adapter->delete(
                $this->getAttrTable('name'),
                new Zend_Db_Expr(implode(' OR ', $where))
            );
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Recreates the array that will be used to insert attribute values for specific tag
     * Returning array is an array($attrcode => array($storeId=>$value),..);
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $data - data that needs to be updated
     * @return array
     */
    protected function _prepareTagDataForInsert(array $data)
    {
        unset($data['default']);

        $insertValues = array();
        $insertValues['name'] = $data;
        $insertValues['url_key'] = array();

        $tag = $this->getTag();
        foreach ($data as $storeId => $name) {
            $insertValues['url_key'][$storeId] = $tag->formatUrlKey($name);
        }

        return $insertValues;
    }

    /**
     * Insert or Update attribute data
     * In case the user edits the name of a tag or ads a new one, then we`ll have to update 2 attributes: name and url
     * 
     * @param Evozon_Blog_Model_Tag $object
     * @param array $data (has the url_key as well)
     * @return Evozon_Blog_Model_Resource_Tag
     */
    protected function _addAttributeValues(array $data)
    {
        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we have no aftersave action
         */
        if (Mage::app()->isSingleStoreMode()) {
            return $this;
        }

        $attributes = array('name', 'url_key');
        $bind = array();

        $tag = $this->getTag();
        $table = $this->getAttrTable('name');
        try {
            foreach ($attributes as $attrCode) {
                foreach ($data[$attrCode] as $storeId => $value) {
                    $bind[] = array(
                        'entity_type_id' => $tag->getEntityTypeId(),
                        'attribute_id' => $this->getAttrId($attrCode),
                        'store_id' => $storeId,
                        'entity_id' => $tag->getEntityId(),
                        'value' => $value
                    );
                }
            }

            if ($bind) {
                $this->_getWriteAdapter()->insertOnDuplicate($table, $bind, array('value'));
            }
        } catch (Exception $exc) {
            throw $exc;
        }

        return $this;
    }
    
    /**
     * Setting default count (0) for all the stores
     * 
     * @return \Evozon_Blog_Model_Resource_Tag
     * @throws Exception
     */
    protected function _addDefaultCountOnNewObject()
    {
        if (!$this->getTag()->getIsNew() || Mage::app()->isSingleStoreMode())
        {
            return $this;
        }
        
        $bind = array();
        $tag = $this->getTag();
        $table = $this->getAttrTable('count');
        $attrId = $this->getAttrId('count');
        $storeIds  = Mage::getResourceModel('core/store_collection')->getAllIds();
        try {
            foreach ($storeIds as $storeId)
            {
                $bind[] = array(
                    'entity_type_id' => $tag->getEntityTypeId(),
                    'attribute_id' => $attrId,
                    'store_id' => $storeId,
                    'entity_id' => $tag->getEntityId(),
                    'value' => 0
                );
            }
            
            $this->_getWriteAdapter()->insertMultiple($table, $bind);
        } catch (Exception $exc) {
            throw $exc;
        }
        
        return $this;
    }

    /**
     * After saving post-tag relations (or deleting them), the Count attribute has to be updated as well
     * The Count depends on the store
     * 
     * @param array $tagsAndStoresToChangeCountOn
     * @param string $operator '+' | '-'
     * @return \Evozon_Blog_Model_Resource_Tag
     * @throws Exception
     */
    public function updateCountOnMultipleStores(array $tagsAndStoresToChangeCountOn, $operator)
    {
        $attribute = $this->getAttribute('count');

        $bind = array();
        foreach ($tagsAndStoresToChangeCountOn as $tagId=>$storeIds) {
            foreach ($storeIds as $storeId) {
                $bind[] = array(
                    'entity_type_id' => $attribute->getEntityTypeId(),
                    'attribute_id' => $attribute->getAttributeId(),
                    'store_id' => $storeId,
                    'entity_id' => $tagId,
                    'value' => new Zend_Db_Expr('IF(value > 0, value ' . $operator . ' 1 , 1)')
                );
            }
        }
        
        $this->_updateCount($bind, $operator);
        return $this;
    }
    
    /**
     * After saving post-tag relations (or deleting them), the Count attribute has to be updated as well
     * The Count depends on the store
     * 
     * @param array $tagIds
     * @param int $storeId
     * @param string $operator '+' | '-'
     * @return \Evozon_Blog_Model_Resource_Tag
     * @throws Exception
     */
    public function updateCountOnSingleStore(array $tagIds, $storeId, $operator)
    {
        $attribute = $this->getAttribute('count');

        $bind = array();
        foreach ($tagIds as $tagId) {
            $bind[] = array(
                'entity_type_id' => $attribute->getEntityTypeId(),
                'attribute_id' => $attribute->getAttributeId(),
                'store_id' => $storeId,
                'entity_id' => $tagId,
                'value' => new Zend_Db_Expr('IF(value > 0, value ' . $operator . ' 1 , 1)')
            );
        }

        $this->_updateCount($bind, $operator);
        return $this;
    }
    
    /**
     * Using the binding array, save new count in table
     * 
     * @param array $bind
     * @param string $operator
     * @return \Evozon_Blog_Model_Resource_Tag
     * @throws Exception
     */
    protected function _updateCount($bind, $operator)
    {
        $adapter = $this->_getWriteAdapter();
        $table = $this->getAttribute('count')->getBackend()->getTable();
        
        try {
            if ($bind) {
                $adapter->insertOnDuplicate($table, $bind,
                    array('value' => new Zend_Db_Expr('value ' . $operator . ' 1')));
            }
        } catch (Exception $exc) {
            throw $exc;
        }


        return $this;
    }

    /**
     * Gets minimum and maximum range for tags
     * in order to be able to set frontend style
     * and make the most common ones - more visible and vice-versa
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $storeId
     * @return array
     */
    public function getRange()
    {
        $adapter = $this->_getReadAdapter();

        $attr = $this->getAttribute('count');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();

        $select = $adapter->select()
            ->from(array('a' => $attrTable), array('value'))
            ->where('attribute_id = ?', $attrId)
            ->where('store_id = ?', Mage::app()->getStore()->getStoreId());

        $rangeSelect = $adapter->select()
            ->from(array('t' => $select));

        $rangeSelect
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(new Zend_Db_Expr("MIN(`t`.`value`) as min"))
            ->columns(new Zend_Db_Expr("MAX(`t`.`value`) as max"));

        return $adapter->fetchRow($rangeSelect);
    }

    /**
     * Accessed from controller,
     * it is called upon selecting filtering by tag
     * In case of store switch, the filtering should still be done
     * So we need the id of the tag entity that has been used
     * The tag always has a default value for the urlKey and it might have a store specific one
     * The joins are made to assure the required data has been selected and will be displayed
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_TagController
     * @param string $urlKey
     * @param int $storeId
     * @return array
     */
    public function getEntityIdByUrlKey($urlKey, $storeId)
    {
        $adapter = $this->_getReadAdapter();

        $attr = $this->getAttribute('url_key');
        $attrId = $attr->getAttributeId();
        $attrTable = $attr->getBackend()->getTable();

        $idSelect = $adapter
            ->select()
            ->from($attrTable, array('entity_id'))
            ->where('attribute_id = ?', $attrId)
            ->where('value = ?', $urlKey);

        $defaultConditions = array(
            $adapter->quoteInto("value_default.entity_id = t.entity_id", ''),
            $adapter->quoteInto("value_default.attribute_id = ?", $attrId),
            $adapter->quoteInto("value_default.store_id = ?", 0)
        );

        $leftJoin = array(
            $adapter->quoteInto("value.entity_id = t.entity_id", ''),
            $adapter->quoteInto("value.attribute_id = ?", $attrId),
            $adapter->quoteInto("value.store_id = ?", $storeId)
        );

        $query = $adapter
            ->select()
            ->from(
                array('value_default' => $attrTable), array('default' => 'value')
            )
            ->joinInner(
                array('t' => new Zend_Db_Expr("(" . $idSelect . ")")),
                't.entity_id=value_default.entity_id'
            )
            ->joinLeft(
                array('value' => $attrTable),
                implode(' AND ', $leftJoin),
                array('store' => 'value')
            )
            ->where(implode(' AND ', $defaultConditions));

        return $adapter->fetchRow($query);
    }

}
