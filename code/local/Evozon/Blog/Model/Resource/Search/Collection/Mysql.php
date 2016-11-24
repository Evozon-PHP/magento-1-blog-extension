<?php
/**
 * Blog Search Collection Database
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Collection_Mysql extends Evozon_Blog_Model_Resource_Search_Collection_Abstract
{
    /**
     * Entity object to define collection's attributes
     *
     * @var Mage_Eav_Model_Entity_Abstract
     */
    protected $_entity;

    /**
     * Cached resources singleton
     *
     * @var Mage_Core_Model_Resource
     */
    protected $_resources;

    /**
     * Main table name
     *
     * @var string
     */
    protected $_mainTable;

    /**
     * @var $_storeId
     */
    protected $_storeId;

    /**
     * @var $_useAnalyticFunction
     */
    protected $_useAnalyticFunction;

    /**
     * @var $_castToIntMap
     */
    protected $_castToIntMap;


    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Resource_Abstract $resource
     */
    public function __construct($resource = null)
    {
        parent::__construct();
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
        $this->_init('evozon_blog/post');
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

        $this->_setMainTable('evozon_blog/search_fulltext', 'post_id');

        return $this;
    }

    /**
     * Get collection's entity object
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     * @throws Mage_Core_Exception
     */
    public function getEntity()
    {
        if (empty($this->_entity)) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Entity is not initialized'));
        }
        return $this->_entity;
    }

    /**
     * Set entity to use for attributes
     *
     * @param Mage_Eav_Model_Entity_Abstract $entity
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     * @throws Mage_Core_Exception
     * @throw Mage_Eav_Exception
     */
    public function setEntity($entity)
    {
        if ($entity instanceof Mage_Eav_Model_Entity_Abstract) {
            $this->_entity = $entity;
        } elseif (is_string($entity) || $entity instanceof Mage_Core_Model_Config_Element) {
            $this->_entity = Mage::getModel('eav/entity')->setType($entity);
        } else {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity supplied: %s', print_r($entity, 1)));
        }
        return $this;
    }

    /**
     * Prepare static entity fields
     *
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    protected function _prepareStaticFields()
    {
        foreach ($this->getEntity()->getDefaultAttributes() as $field) {
            $this->_staticFields[$field] = $field;
        }
        return $this;
    }

    /**
     * Init select
     *
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array('e' => $this->getEntity()->getEntityTable()));
        if ($this->getEntity()->getTypeId()) {
            $this->addAttributeToFilter('entity_type_id', $this->getEntity()->getTypeId());
        }
        return $this;
    }

    /**
     * Add attribute filter to collection
     *
     * @param Mage_Eav_Model_Entity_Attribute_Interface|integer|string|array $attribute
     * @param null|string|array $condition
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        if ($attribute === null) {
            $this->getSelect();
            return $this;
        }

        if (is_numeric($attribute)) {
            $attribute = $this->getEntity()->getAttribute($attribute)->getAttributeCode();
        } else if ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Interface) {
            $attribute = $attribute->getAttributeCode();
        }

        if (is_array($attribute)) {
            $sqlArr = array();
            foreach ($attribute as $condition) {
                $sqlArr[] = $this->_getAttributeConditionSql($condition['attribute'], $condition, $joinType);
            }
            $conditionSql = '('.implode(') OR (', $sqlArr).')';
        } else if (is_string($attribute)) {
            if ($condition === null) {
                $condition = '';
            }
            $conditionSql = $this->_getAttributeConditionSql($attribute, $condition, $joinType);
        }

        if (!empty($conditionSql)) {
            $this->getSelect()->where($conditionSql, null, Varien_Db_Select::TYPE_CONDITION);
        } else {
            Mage::throwException('Invalid attribute identifier for filter ('.get_class($attribute).')');
        }

        return $this;
    }

    /**
     * Retrieve entity attribute
     *
     * @param   string $attributeCode
     * @return  Evozon_Blog_Model_Resource_Collection_Database
     */
    public function getAttribute($attributeCode)
    {
        if (isset($this->_joinAttributes[$attributeCode])) {
            return $this->_joinAttributes[$attributeCode]['attribute'];
        }

        return $this->getEntity()->getAttribute($attributeCode);
    }

    /**
     * Get condition sql for the attribute
     *
     * @see self::_getConditionSql
     * @param string $attribute
     * @param mixed $condition
     * @param string $joinType
     * @return string
     */
    protected function _getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        if (isset($this->_joinFields[$attribute])) {

            return $this->_getConditionSql($this->_getAttributeFieldName($attribute), $condition);
        }
        if (isset($this->_staticFields[$attribute])) {
            return $this->_getConditionSql($this->getConnection()->quoteIdentifier('e.' . $attribute), $condition);
        }
        // process linked attribute
        if (isset($this->_joinAttributes[$attribute])) {
            $entity      = $this->getAttribute($attribute)->getEntity();
            $entityTable = $entity->getEntityTable();
        } else {
            $entity      = $this->getEntity();
            $entityTable = 'e';
        }

        if ($entity->isAttributeStatic($attribute)) {
            $conditionSql = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier('e.' . $attribute),
                $condition
            );
        } else {
            $this->_addAttributeJoin($attribute, $joinType);
            if (isset($this->_joinAttributes[$attribute]['condition_alias'])) {
                $field = $this->_joinAttributes[$attribute]['condition_alias'];
            } else {
                $field = $this->_getAttributeTableAlias($attribute) . '.value';

            }

            $conditionSql = $this->_getConditionSql($field, $condition);
        }

        return $conditionSql;
    }

    /**
     * Retreive attribute field name by attribute code
     *
     * @param string $attributeCode
     * @return string
     * @throws Mage_Core_Exception
     */
    protected function _getAttributeFieldName($attributeCode)
    {
        $attributeCode = trim($attributeCode);
        if (isset($this->_joinAttributes[$attributeCode]['condition_alias'])) {
            return $this->_joinAttributes[$attributeCode]['condition_alias'];
        }
        if (isset($this->_staticFields[$attributeCode])) {
            return sprintf('e.%s', $attributeCode);
        }
        if (isset($this->_joinFields[$attributeCode])) {
            $attr = $this->_joinFields[$attributeCode];
            return $attr['table'] ? $attr['table'] . '.' . $attr['field'] : $attr['field'];
        }

        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute name: %s', $attributeCode));
        }

        if ($attribute->isStatic()) {
            if (isset($this->_joinAttributes[$attributeCode])) {
                $fieldName = $this->_getAttributeTableAlias($attributeCode) . '.' . $attributeCode;
            } else {
                $fieldName = 'e.' . $attributeCode;
            }
        } else {
            $fieldName = $this->_getAttributeTableAlias($attributeCode) . '.value';
        }

        return $fieldName;
    }

    /**
     * Get alias for attribute value table
     *
     * @param string $attributeCode
     * @return string
     */
    protected function _getAttributeTableAlias($attributeCode)
    {
        return 'at_' . $attributeCode;
    }

    /**
     * Add attribute value table to the join if it wasn't added previously
     *
     * @param   string $attributeCode
     * @param   string $joinType inner|left
     * @throws  Mage_Eav_Exception
     * @return  Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _addAttributeJoin($attributeCode, $joinType = 'inner')
    {
        if (!empty($this->_filterAttributes[$attributeCode])) {
            return $this;
        }

        $adapter = $this->getConnection();

        $attrTable = $this->_getAttributeTableAlias($attributeCode);
        if (isset($this->_joinAttributes[$attributeCode])) {
            $attribute      = $this->_joinAttributes[$attributeCode]['attribute'];
            $entity         = $attribute->getEntity();
            $entityIdField  = $entity->getEntityIdField();
            $fkName         = $this->_joinAttributes[$attributeCode]['bind'];
            $fkAttribute    = $this->_joinAttributes[$attributeCode]['bindAttribute'];
            $fkTable        = $this->_getAttributeTableAlias($fkName);

            if ($fkAttribute->getBackend()->isStatic()) {
                if (isset($this->_joinAttributes[$fkName])) {
                    $fk = $fkTable . '.' . $fkAttribute->getAttributeCode();
                } else {
                    $fk = 'e.' . $fkAttribute->getAttributeCode();
                }
            } else {
                $this->_addAttributeJoin($fkAttribute->getAttributeCode(), $joinType);
                $fk = $fkTable . '.value';
            }
            $pk = $attrTable . '.' . $this->_joinAttributes[$attributeCode]['filter'];
        } else {
            $entity         = $this->getEntity();
            $entityIdField  = $entity->getEntityIdField();
            $attribute      = $entity->getAttribute($attributeCode);
            $fk             = 'e.' . $entityIdField;
            $pk             = $attrTable . '.' . $entityIdField;
        }

        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute name: %s', $attributeCode));
        }

        if ($attribute->getBackend()->isStatic()) {
            $attrFieldName = $attrTable . '.' . $attribute->getAttributeCode();
        } else {
            $attrFieldName = $attrTable . '.value';
        }

        $fk = $adapter->quoteColumnAs($fk, null);
        $pk = $adapter->quoteColumnAs($pk, null);

        $condArr = array("$pk = $fk");
        if (!$attribute->getBackend()->isStatic()) {
            $condArr[] = $this->getConnection()->quoteInto(
                $adapter->quoteColumnAs("$attrTable.attribute_id", null) . ' = ?', $attribute->getId());
        }

        /**
         * process join type
         */
        $joinMethod = ($joinType == 'left') ? 'joinLeft' : 'join';

        $this->_joinAttributeToSelect($joinMethod, $attribute, $attrTable, $condArr, $attributeCode, $attrFieldName);

        $this->removeAttributeToSelect($attributeCode);
        $this->_filterAttributes[$attributeCode] = $attribute->getId();

        /**
         * Fix double join for using same as filter
         */
        $this->_joinFields[$attributeCode] = array(
            'table' => '',
            'field' => $attrFieldName,
        );

        return $this;
    }

    /**
     * Adding join statement to collection select instance
     *
     * @param   string $method
     * @param   object $attribute
     * @param   string $tableAlias
     * @param   array $condition
     * @param   string $fieldCode
     * @param   string $fieldAlias
     *
     * @return  Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        $this->getSelect()->$method(
            array($tableAlias => $attribute->getBackend()->getTable()),
            '('.implode(') AND (', $condition).')',
            array($fieldCode => $fieldAlias)
        );
        return $this;
    }

    /**
     * Remove an attribute from selection list
     *
     * @param string $attribute
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function removeAttributeToSelect($attribute = null)
    {
        if ($attribute === null) {
            $this->_selectAttributes = array();
        } else {
            unset($this->_selectAttributes[$attribute]);
        }
        return $this;
    }

    /**
     * Add attribute to entities in collection
     *
     * If $attribute=='*' select all attributes
     *
     * @param   array|string|integer|Mage_Core_Model_Config_Element $attribute
     * @param bool|false|string $joinType flag for joining attribute
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     * @throws Mage_Core_Exception
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        if (is_array($attribute)) {
            Mage::getSingleton('eav/config')->loadCollectionAttributes($this->getEntity()->getType(), $attribute);
            foreach ($attribute as $a) {
                $this->addAttributeToSelect($a, $joinType);
            }
            return $this;
        }
        if ($joinType !== false && !$this->getEntity()->getAttribute($attribute)->isStatic()) {
            $this->_addAttributeJoin($attribute, $joinType);
        } elseif ('*' === $attribute) {
            $entity = clone $this->getEntity();
            $attributes = $entity
                ->loadAllAttributes()
                ->getAttributesByCode();
            foreach ($attributes as $attrCode=>$attr) {
                $this->_selectAttributes[$attrCode] = $attr->getId();
            }
        } else {
            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            } else {
                $attrInstance = Mage::getSingleton('eav/config')
                    ->getCollectionAttribute($this->getEntity()->getType(), $attribute);
            }
            if (empty($attrInstance)) {
                throw Mage::exception(
                    'Mage_Eav',
                    Mage::helper('eav')->__('Invalid attribute requested: %s', (string)$attribute)
                );
            }
            $this->_selectAttributes[$attrInstance->getAttributeCode()] = $attrInstance->getId();
        }
        return $this;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)
            ->setPageSize($pageSize);
        return $this;
    }

    /**
     * Set Store scope for collection
     *
     * @param mixed $store
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    public function setStore($store)
    {

        $this->getEntity()->setStoreId($this->getStoreId());

        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            $this->setStoreId(Mage::app()->getStore()->getId());
        }
        return $this->_storeId;
    }

    /**
     * Add search query filter
     *
     * @param string $query
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function addSearchFilter($query)
    {
        $postIds = Mage::getSingleton('evozon_blog/search_engine_mysql')->prepareResult();
        $this->addFieldToFilter('entity_id', array('in'=>$postIds));

        return $this;
    }

    /**
     * Wrapper for compatibility with Varien_Data_Collection_Db
     *
     * @param mixed $attribute
     * @param mixed $condition
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        return $this->addAttributeToFilter($attribute, $condition);
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return $this->_getConnection('write');
    }

    /**
     * Get connection by name or type
     *
     * @param string $connectionName
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getConnection($connectionName)
    {
        if (isset($this->_connections[$connectionName])) {
            return $this->_connections[$connectionName];
        }
        if (!empty($this->_resourcePrefix)) {
            $this->_connections[$connectionName] = $this->_resources->getConnection(
                $this->_resourcePrefix . '_' . $connectionName);
        } else {
            $this->_connections[$connectionName] = $this->_resources->getConnection($connectionName);
        }

        return $this->_connections[$connectionName];
    }

    /**
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter
     *
     * @return string
     */
    public function getMainTable()
    {
        if (empty($this->_mainTable)) {
            Mage::throwException(Mage::helper('core')->__('Empty main table name'));
        }
        return $this->getTable($this->_mainTable);
    }

    /**
     * Set main entity table name and primary key field name
     * If field name is ommited {table_name}_id will be used
     *
     * @param string $mainTable
     * @param string|null $idFieldName
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _setMainTable($mainTable, $idFieldName = null)
    {
        $mainTableArr = explode('/', $mainTable);

        if (!empty($mainTableArr[1])) {
            if (empty($this->_resourceModel)) {
                $this->_setResource($mainTableArr[0]);
            }
            $this->_setMainTable($mainTableArr[1], $idFieldName);
        } else {
            $this->_mainTable = $mainTable;
            if (is_null($idFieldName)) {
                $idFieldName = $mainTable . '_id';
            }
            $this->_idFieldName = $idFieldName;
        }

        return $this;
    }

    /**
     * Get table name for the entity, validated by db adapter
     *
     * @param string $entityName
     * @return string
     */
    public function getTable($entityName)
    {
        if (is_array($entityName)) {
            $cacheName    = join('@', $entityName);
            list($entityName, $entitySuffix) = $entityName;
        } else {
            $cacheName    = $entityName;
            $entitySuffix = null;
        }

        if (isset($this->_tables[$cacheName])) {
            return $this->_tables[$cacheName];
        }

        if (strpos($entityName, '/')) {
            if (!is_null($entitySuffix)) {
                $modelEntity = array($entityName, $entitySuffix);
            } else {
                $modelEntity = $entityName;
            }
            $this->_tables[$cacheName] = $this->_resources->getTableName($modelEntity);
        } else if (!empty($this->_resourceModel)) {
            $entityName = sprintf('%s/%s', $this->_resourceModel, $entityName);
            if (!is_null($entitySuffix)) {
                $modelEntity = array($entityName, $entitySuffix);
            } else {
                $modelEntity = $entityName;
            }
            $this->_tables[$cacheName] = $this->_resources->getTableName($modelEntity);
        } else {
            if (!is_null($entitySuffix)) {
                $entityName .= '_' . $entitySuffix;
            }
            $this->_tables[$cacheName] = $entityName;
        }
        return $this->_tables[$cacheName];
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        $writeAdapter = $this->_getWriteAdapter();
        if ($writeAdapter && $writeAdapter->getTransactionLevel() > 0) {
            // if transaction is started we should use write connection for reading
            return $writeAdapter;
        }
        return $this->_getConnection('read');
    }

    public function addEntityTypeToSelect($entityType, $prefix)
    {
        $this->_selectEntityTypes[$entityType] = array(
            'prefix' => $prefix,
        );
        return $this;
    }

    /**
     * Add field to static
     *
     * @param string $field
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function addStaticField($field)
    {
        if (!isset($this->_staticFields[$field])) {
            $this->_staticFields[$field] = $field;
        }
        return $this;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     *
     * @param string $alias
     * @param string $expression
     * @param string $attribute
     * @return Evozon_Blog_Model_Resource_Collection_Database
     * @throws Mage_Core_Exception
     */
    public function addExpressionAttributeToSelect($alias, $expression, $attribute)
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw Mage::exception(
                'Mage_Eav',
                Mage::helper('eav')->__('Joint field or attribute expression with this alias is already declared')
            );
        }
        if (!is_array($attribute)) {
            $attribute = array($attribute);
        }

        $fullExpression = $expression;
        // Replacing multiple attributes
        foreach ($attribute as $attributeItem) {
            if (isset($this->_staticFields[$attributeItem])) {
                $attrField = sprintf('e.%s', $attributeItem);
            } else {
                $attributeInstance = $this->getAttribute($attributeItem);

                if ($attributeInstance->getBackend()->isStatic()) {
                    $attrField = 'e.' . $attributeItem;
                } else {
                    $this->_addAttributeJoin($attributeItem, 'left');
                    $attrField = $this->_getAttributeFieldName($attributeItem);
                }
            }

            $fullExpression = str_replace('{{attribute}}', $attrField, $fullExpression);
            $fullExpression = str_replace('{{' . $attributeItem . '}}', $attrField, $fullExpression);
        }

        $this->getSelect()->columns(array($alias => $fullExpression));

        $this->_joinFields[$alias] = array(
            'table' => false,
            'field' => $fullExpression
        );

        return $this;
    }

    /**
     * Groups results by specified attribute
     *
     * @param string|array $attribute
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function groupByAttribute($attribute)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attributeItem) {
                $this->groupByAttribute($attributeItem);
            }
        } else {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->group($this->_getAttributeFieldName($attribute));
                return $this;
            }

            if (isset($this->_staticFields[$attribute])) {
                $this->getSelect()->group(sprintf('e.%s', $attribute));
                return $this;
            }

            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
                $entityField = $this->_getAttributeTableAlias($attribute) . '.' . $attrInstance->getAttributeCode();
            } else {
                $attrInstance = $this->getEntity()->getAttribute($attribute);
                $entityField = 'e.' . $attribute;
            }

            if ($attrInstance->getBackend()->isStatic()) {
                $this->getSelect()->group($entityField);
            } else {
                $this->_addAttributeJoin($attribute);
                $this->getSelect()->group($this->_getAttributeTableAlias($attribute).'.value');
            }
        }

        return $this;
    }

    /**
     * Add attribute from joined entity to select
     *
     * @param string $alias alias for the joined attribute
     * @param string|Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function joinAttribute($alias, $attribute, $bind, $filter=null, $joinType='inner', $storeId=null)
    {
        // validate alias
        if (isset($this->_joinAttributes[$alias])) {
            throw Mage::exception(
                'Mage_Eav',
                Mage::helper('eav')->__('Invalid alias, already exists in joint attributes')
            );
        }

        // validate bind attribute
        if (is_string($bind)) {
            $bindAttribute = $this->getAttribute($bind);
        }

        if (!$bindAttribute || (!$bindAttribute->isStatic() && !$bindAttribute->getId())) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid foreign key'));
        }

        // try to explode combined entity/attribute if supplied
        if (is_string($attribute)) {
            $attrArr = explode('/', $attribute);
            if (isset($attrArr[1])) {
                $entity    = $attrArr[0];
                $attribute = $attrArr[1];
            }
        }

        // validate entity
        if (empty($entity) && $attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $entity = $attribute->getEntity();
        } elseif (is_string($entity)) {
            // retrieve cached entity if possible
            if (isset($this->_joinEntities[$entity])) {
                $entity = $this->_joinEntities[$entity];
            } else {
                $entity = Mage::getModel('eav/entity')->setType($attrArr[0]);
            }
        }
        if (!$entity || !$entity->getTypeId()) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid entity type'));
        }

        // cache entity
        if (!isset($this->_joinEntities[$entity->getType()])) {
            $this->_joinEntities[$entity->getType()] = $entity;
        }

        // validate attribute
        if (is_string($attribute)) {
            $attribute = $entity->getAttribute($attribute);
        }
        if (!$attribute) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid attribute type'));
        }

        if (empty($filter)) {
            $filter = $entity->getEntityIdField();
        }

        // add joined attribute
        $this->_joinAttributes[$alias] = array(
            'bind'          => $bind,
            'bindAttribute' => $bindAttribute,
            'attribute'     => $attribute,
            'filter'        => $filter,
            'store_id'      => $storeId,
        );

        $this->_addAttributeJoin($alias, $joinType);

        return $this;
    }

    /**
     * Join regular table field and use an attribute as fk
     *
     * @param string $alias 'country_name'
     * @param string $table 'directory/country_name'
     * @param string $field 'name'
     * @param string $bind 'PK(country_id)=FK(shipping_country_id)'
     * @param string|array $cond "{{table}}.language_code='en'" OR array('language_code'=>'en')
     * @param string $joinType 'left'
     * @throws Mage_Core_Exception
     * @return Evozon_Blog_Model_Resource_Search_Collection_Database
     */
    public function joinField($alias, $table, $field, $bind, $cond=null, $joinType='inner')
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw Mage::exception(
                'Mage_Eav',
                Mage::helper('eav')->__('Joined field with this alias is already declared')
            );
        }

        // validate table
        if (strpos($table, '/')!==false) {
            $table = Mage::getSingleton('core/resource')->getTableName($table);
        }
        $tableAlias = $this->_getAttributeTableAlias($alias);

        // validate bind
        list($pk, $fk) = explode('=', $bind);
        $pk = $this->getSelect()->getAdapter()->quoteColumnAs(trim($pk), null);
        $bindCond = $tableAlias . '.' . trim($pk) . '=' . $this->_getAttributeFieldName(trim($fk));

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = array($bindCond);

        // add where condition if needed
        if ($cond !== null) {
            if (is_array($cond)) {
                foreach ($cond as $k=>$v) {
                    $condArr[] = $this->_getConditionSql($tableAlias.'.'.$k, $v);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '(' . implode(') AND (', $condArr) . ')';

        // join table
        $this->getSelect()
            ->$joinMethod(array($tableAlias => $table), $cond, ($field ? array($alias=>$field) : array()));

        // save joined attribute
        $this->_joinFields[$alias] = array(
            'table' => $tableAlias,
            'field' => $field,
        );

        return $this;
    }

    /**
     * Join a table
     *
     * @param string|array $table
     * @param string $bind
     * @param string|array $fields
     * @param null|array $cond
     * @param string $joinType
     * @return Evozon_Blog_Model_Resource_Collection_Database
     * @throws Mage_Core_Exception
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        $tableAlias = null;
        if (is_array($table)) {
            list($tableAlias, $tableName) = each($table);
        } else {
            $tableName = $table;
        }

        // validate table
        if (strpos($tableName, '/') !== false) {
            $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);
        }
        if (empty($tableAlias)) {
            $tableAlias = $tableName;
        }

        // validate fields and aliases
        if (!$fields) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Invalid joint fields'));
        }
        foreach ($fields as $alias=>$field) {
            if (isset($this->_joinFields[$alias])) {
                throw Mage::exception(
                    'Mage_Eav',
                    Mage::helper('eav')->__('A joint field with this alias (%s) is already declared', $alias)
                );
            }
            $this->_joinFields[$alias] = array(
                'table' => $tableAlias,
                'field' => $field,
            );
        }

        // validate bind
        list($pk, $fk) = explode('=', $bind);
        $bindCond = $tableAlias . '.' . $pk . '=' . $this->_getAttributeFieldName($fk);

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = array($bindCond);

        // add where condition if needed
        if ($cond !== null) {
            if (is_array($cond)) {
                foreach ($cond as $k => $v) {
                    $condArr[] = $this->_getConditionSql($tableAlias.'.'.$k, $v);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '('.implode(') AND (', $condArr).')';

        $this->getSelect()->$joinMethod(array($tableAlias => $tableName), $cond, $fields);

        return $this;
    }

    /**
     * Load collection data into object items
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
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
     * Treat "order by" items as attributes to sort
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $attribute => $direction) {
                $this->addAttributeToSort($attribute, $direction);
            }
            $this->_isOrdersRendered = true;
        }
        return $this;
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (isset($this->_joinFields[$attribute])) {
            $this->getSelect()->order($this->_getAttributeFieldName($attribute).' '.$dir);
            return $this;
        }
        if (isset($this->_staticFields[$attribute])) {
            $this->getSelect()->order("e.{$attribute} {$dir}");
            return $this;
        }
        if (isset($this->_joinAttributes[$attribute])) {
            $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            $entityField = $this->_getAttributeTableAlias($attribute) . '.' . $attrInstance->getAttributeCode();
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            $entityField = 'e.' . $attribute;
        }

        if ($attrInstance) {
            if ($attrInstance->getBackend()->isStatic()) {
                $orderExpr = $entityField;
            } else {
                $this->_addAttributeJoin($attribute, 'left');
                if (isset($this->_joinAttributes[$attribute])||isset($this->_joinFields[$attribute])) {
                    $orderExpr = $attribute;
                } else {
                    $orderExpr = $this->_getAttributeTableAlias($attribute).'.value';
                }
            }

            if (is_array($this->_castToIntMap)  && in_array($attrInstance->getFrontendClass(), $this->_castToIntMap)) {
                $orderExpr = Mage::getResourceHelper('eav')->getCastToIntExpression(
                    $this->_prepareOrderExpression($orderExpr)
                );
            }

            $orderExpr .= ' ' . $dir;
            $this->getSelect()->order($orderExpr);
        }
        return $this;
    }

    /**
     * Retrieve attribute expression by specified column
     *
     * @param string $field
     * @return string|Zend_Db_Expr
     */
    protected function _prepareOrderExpression($field)
    {
        foreach ($this->getSelect()->getPart(Zend_Db_Select::COLUMNS) as $columnEntry) {
            if ($columnEntry[2] != $field) {
                continue;
            }
            if ($columnEntry[1] instanceof Zend_Db_Expr) {
                return $columnEntry[1];
            }
        }
        return $field;
    }

    /**
     * Load entities records into items
     *
     * @throws Exception
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        if ($this->_pageSize) {
            $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
        }

        $this->printLogQuery($printQuery, $logQuery);

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->_prepareSelect($this->getSelect());
            $rows = $this->_fetchAll($query);
        } catch (Exception $e) {
            Mage::printException($e, $query);
            $this->printLogQuery(true, true, $query);
            throw $e;
        }

        foreach ($rows as $v) {
            $object = $this->getNewEmptyItem()
                ->setData($v);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = array($object);
            }
        }

        return $this;
    }

    /**
     * Prepare select for load
     *
     * @param Varien_Db_Select $select OPTIONAL
     * @return string
     */
    public function _prepareSelect(Varien_Db_Select $select)
    {
        if ($this->_useAnalyticFunction) {
            $helper = Mage::getResourceHelper('core');
            return $helper->getQueryUsingAnalyticFunction($select);
        }

        return (string)$select;
    }

    /**
     * Add an object to the collection
     *
     * @param Varien_Object $object
     * @return Evozon_Blog_Model_Resource_Collection_Database
     * @throws Mage_Core_Exception
     */
    public function addItem(Varien_Object $object)
    {
        if (get_class($object) !== $this->_itemObjectClass) {
            throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Attempt to add an invalid object'));
        }
        return parent::addItem($object);
    }

    /**
     * Load attributes into loaded entities
     *
     * @throws Exception
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();

        $tableAttributes = array();
        $attributeTypes  = array();
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        $selects = array();
        foreach ($tableAttributes as $table=>$attributes) {
            $select = $this->_getLoadAttributesSelect($table, $attributes);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table]
            );
        }
        $selectGroups = Mage::getResourceHelper('eav')->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                try {
                    $select = implode(' UNION ALL ', $selects);
                    $values = $this->getConnection()->fetchAll($select);
                } catch (Exception $e) {
                    Mage::printException($e, $select);
                    $this->printLogQuery(true, true, $select);
                    throw $e;
                }

                foreach ($values as $value) {
                    $this->_setItemAttributeValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve attributes load select
     *
     * @param   string $table
     * @return  Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = array())
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        $helper = Mage::getResourceHelper('eav');
        $entityIdField = $this->getEntity()->getEntityIdField();
        $select = $this->getConnection()->select()
            ->from($table, array($entityIdField, 'attribute_id'))
            ->where('entity_type_id =?', $this->getEntity()->getTypeId())
            ->where("$entityIdField IN (?)", array_keys($this->_itemsById))
            ->where('attribute_id IN (?)', $attributeIds);
        return $select;
    }

    /**
     * @param Varien_Db_Select $select
     * @param string $table
     * @param string $type
     * @return Varien_Db_Select
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        $helper = Mage::getResourceHelper('eav');
        $select->columns(array(
            'value' => $helper->prepareEavAttributeValue($table. '.value', $type),
        ));

        return $select;
    }

    /**
     * Initialize entity object property value
     *
     * @param   array $valueInfo
     * @return Evozon_Blog_Model_Resource_Collection_Database
     * @throws Mage_Core_Exception
     */
    protected function _setItemAttributeValue($valueInfo)
    {
        $entityIdField  = $this->getEntity()->getEntityIdField();
        $entityId       = $valueInfo[$entityIdField];
        if (!isset($this->_itemsById[$entityId])) {
            throw Mage::exception('Mage_Eav',
                Mage::helper('eav')->__('Data integrity: No header row found for attribute')
            );
        }
        $attributeCode = array_search($valueInfo['attribute_id'], $this->_selectAttributes);
        if (!$attributeCode) {
            $attribute = Mage::getSingleton('eav/config')->getCollectionAttribute(
                $this->getEntity()->getType(),
                $valueInfo['attribute_id']
            );
            $attributeCode = $attribute->getAttributeCode();
        }

        foreach ($this->_itemsById[$entityId] as $object) {
            $object->setData($attributeCode, $valueInfo['value']);
        }

        return $this;
    }

    /**
     * After load method
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _afterLoad()
    {
        return $this;
    }

    /**
     * Retrive all ids for collection
     *
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Clone and reset collection
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
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

    /**
     * Retrive all ids sql
     *
     * @return array
     */
    public function getAllIdsSql()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->reset(Zend_Db_Select::GROUP);
        $idsSelect->columns('e.'.$this->getEntity()->getIdFieldName());

        return $idsSelect;
    }

    /**
     * Save all the entities in the collection
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function save()
    {
        foreach ($this->getItems() as $item) {
            $item->save();
        }
        return $this;
    }

    /**
     * Delete all the entities in the collection
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function delete()
    {
        foreach ($this->getItems() as $k=>$item) {
            $this->getEntity()->delete($item);
            unset($this->_items[$k]);
        }
        return $this;
    }

    /**
     * Import 2D array into collection as objects
     *
     * If the imported items already exist, update the data for existing objects
     *
     * @param array $arr
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function importFromArray($arr)
    {
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($arr as $row) {
            $entityId = $row[$entityIdField];
            if (!isset($this->_items[$entityId])) {
                $this->_items[$entityId] = $this->getNewEmptyItem();
                $this->_items[$entityId]->setData($row);
            } else {
                $this->_items[$entityId]->addData($row);
            }
        }
        return $this;
    }

    /**
     * Get collection data as a 2D array
     *
     * @return array
     */
    public function exportToArray()
    {
        $result = array();
        $entityIdField = $this->getEntity()->getEntityIdField();
        foreach ($this->getItems() as $item) {
            $result[$item->getData($entityIdField)] = $item->getData();
        }
        return $result;
    }

    /**
     * Retreive row id field name
     *
     * @return string
     */
    public function getRowIdFieldName()
    {
        if ($this->_idFieldName === null) {
            $this->_setIdFieldName($this->getEntity()->getIdFieldName());
        }
        return $this->getIdFieldName();
    }

    /**
     * Set row id field name
     * @param string $fieldName
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function setRowIdFieldName($fieldName)
    {
        return $this->_setIdFieldName($fieldName);
    }

    /**
     * Set sorting order
     *
     * $attribute can also be an array of attributes
     *
     * @param string|array $attribute
     * @param string $dir
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                parent::setOrder($attr, $dir);
            }
        }
        return parent::setOrder($attribute, $dir);
    }

    /**
     * Retreive array of attributes
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray($arrAttributes = array())
    {
        $arr = array();
        foreach ($this->_items as $k => $item) {
            $arr[$k] = $item->toArray($arrAttributes);
        }
        return $arr;
    }

    /**
     * Returns already loaded element ids
     *
     * return array
     */
    public function getLoadedIds()
    {
        return array_keys($this->_items);
    }

    /**
     * Initialize connections and tables for this resource model
     * If one or both arguments are string, will be used as prefix
     * If $tables is null and $connections is string, $tables will be the same
     *
     * @param string|array $connections
     * @param string|array|null $tables
     * @return Mage_Core_Model_Resource_Abstract
     */
    protected function _setResource($connections, $tables = null)
    {
        $this->_resources = Mage::getSingleton('core/resource');

        if (is_array($connections)) {
            foreach ($connections as $k=>$v) {
                $this->_connections[$k] = $this->_resources->getConnection($v);
            }
        } else if (is_string($connections)) {
            $this->_resourcePrefix = $connections;
        }

        if (is_null($tables) && is_string($connections)) {
            $this->_resourceModel = $this->_resourcePrefix;
        } else if (is_array($tables)) {
            foreach ($tables as $k => $v) {
                $this->_tables[$k] = $this->_resources->getTableName($v);
            }
        } else if (is_string($tables)) {
            $this->_resourceModel = $tables;
        }
        return $this;
    }

    /**
     * Reset collection
     *
     * @return Evozon_Blog_Model_Resource_Collection_Database
     */
    protected function _reset()
    {
        parent::_reset();

        $this->_selectEntityTypes = array();
        $this->_selectAttributes  = array();
        $this->_filterAttributes  = array();
        $this->_joinEntities      = array();
        $this->_joinAttributes    = array();
        $this->_joinFields        = array();

        return $this;
    }
    
    /**
     * Set the locale dates for posts collection
     * If the function is used from back end the created_at and publish_date will be set,
     * else only the publish_date with the format from system config
     * 
     * @TODO Refactore this part with proper date format
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function setProperDateFormat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $this->setProperDateFormatForAdmin();

            return $this;
        }
        
        $this->setProperDateFormatForCustomer();

        return $this;
    }
    
    /**
     * Set the proper created at and publish date for the back end
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForAdmin()
    {
        foreach ($this->getItems() as $post) {
            $post->setCreatedAt($this->getLocaleDate($post->getCreatedAt()));
            $post->setPublishDate($this->getLocaleDate($post->getPublishDate()));
        }
    }
    
    /**
     * Set the proper publish at date for the front end
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForCustomer()
    {
        foreach ($this->getItems() as $post) {
            $post->setPublishDate($this->getTimeByFormatAndLocale($post->getPublishDate()));
        }
    }
    
    /**
     * Return the date converted in the locale date
     * 
     * @TODO   Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getLocaleDate($date) {
        return Mage::helper('evozon_blog')->getLocaleDate($date);
    }
    
    /**
     * Return the date converted in the locale date and in the default locale from system config
     * 
     * @TODO    Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getTimeByFormatAndLocale($date) {
        return Mage::helper('evozon_blog')->getTimeByFormatAndLocale($date);
    }
}