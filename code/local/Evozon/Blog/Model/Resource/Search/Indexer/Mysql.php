<?php

/**
 * Blog Search Model
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Indexer_Mysql extends  Mage_CatalogSearch_Model_Resource_Fulltext
{

    /**
     * Allowed post status to index
     *
     * @var array
     */
    protected $_indexablePostStatus = array(1);

    /**
     * Collection limit
     *
     * @var int
     */
    protected $_limit = 100;

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/search_fulltext', 'post_id');
        $this->_engine = $this->getEngine();
    }

    /**
     * 
     * @return object
     */
    protected function getEngine()
    {
        if (!$this->_engine) {
            $this->_engine = Mage::getModel('evozon_blog/search')->getConfig()->getEngine();
        }

        return $this->_engine;
    }

    /**
     * @return array
     */
    public function getIndexablePostStatus()
    {
        return $this->_indexablePostStatus;
    }

    /**
     * @param array $indexablePostStatus
     */
    public function setIndexablePostStatus($indexablePostStatus)
    {
        $this->_indexablePostStatus = $indexablePostStatus;
    }

    /**
     * Regenerate search index for specific store
     *
     * @TODO refactory
     * @param int $storeId Store View Id
     * @param null $postIds
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    protected function _rebuildStoreIndex($storeId, $postIds = null)
    {
        $this->cleanIndex($storeId, $postIds);

        // prepare searchable attributes
        $staticFields = array();
        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $staticFields[] = $attribute->getAttributeCode();
        }
        $dynamicFields = array(
            'int' => array_keys($this->_getSearchableAttributes('int')),
            'varchar' => array_keys($this->_getSearchableAttributes('varchar')),
            'text' => array_keys($this->_getSearchableAttributes('text')),
            'decimal' => array_keys($this->_getSearchableAttributes('decimal')),
            'datetime' => array_keys($this->_getSearchableAttributes('datetime')),
        );

        $status = $this->_getSearchableAttribute('status');
        $statusVals = $this->getIndexablePostStatus();

        $lastPostId = 0;
        while (true) {
            $posts = $this->_getSearchablePosts($storeId, $staticFields, $postIds, $lastPostId);
            if (!$posts) {
                break;
            }

            $postAttributes = array();
            foreach ($posts as $postData) {
                $lastPostId = $postData['entity_id'];
                $postAttributes[$postData['entity_id']] = $postData['entity_id'];
            }

            $postIndexes = array();
            $postAttributes = $this->_getPostAttributes($storeId, $postAttributes, $dynamicFields);
            foreach ($posts as $postData) {
                if (!isset($postAttributes[$postData['entity_id']])) {
                    continue;
                }

                $postAttr = $postAttributes[$postData['entity_id']];
                if (!isset($postAttr[$status->getId()]) || !in_array($postAttr[$status->getId()], $statusVals)) {
                    continue;
                }

                $postIndex = array(
                    $postData['entity_id'] => $postAttr
                );

                $index = $this->_preparePostIndex($postIndex, $postData, $storeId);
                $postIndexes[$postData['entity_id']] = $index;
            }

            $this->_savePostIndexes($storeId, $postIndexes);
        }

        $this->resetSearchResults();

        return $this;
    }

    /**
     * Retrieve searchable posts per store
     *
     * @param int $storeId
     * @param array $staticFields
     * @param null $postIds
     * @param int $lastPostId
     *
     * @return array
     */
    protected function _getSearchablePosts($storeId, array $staticFields, $postIds = null, $lastPostId = 0)
    {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();
        $writeAdapter = $this->_getWriteAdapter();

        $select = $writeAdapter->select()
            ->useStraightJoin(true)
            ->from(
                array('e' => $this->getTable('evozon_blog/post')),
                array_merge(array('entity_id'), $staticFields)
            )
            ->join(
                array('website' => $this->getTable('evozon_blog/post_website')),
                $writeAdapter->quoteInto(
                    'website.post_id=e.entity_id AND website.website_id=?',
                    $websiteId
                ),
                array()
            );


        if (!is_null($postIds)) {
            $select->where('e.entity_id IN(?)', $postIds);
        }

        $select->where('e.entity_id>?', $lastPostId)
            ->limit($this->_limit)
            ->order('e.entity_id');


        $result = $writeAdapter->fetchAll($select);

        return $result;
    }
    
    /**
     * Prepare results for query
     *
     * @param Mage_CatalogSearch_Model_Fulltext $object
     * @param string $queryText
     * @param Mage_CatalogSearch_Model_Query $query
     *
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    public function prepareResult($object, $queryText, $query)
    {
        $adapter = $this->_getWriteAdapter();
        
        $searchType = Mage::getModel('evozon_blog/search')->getConfig()->getSearchType();
        $preparedTerms = Mage::getResourceHelper('catalogsearch')
            ->prepareTerms($queryText, $query->getMaxQueryWords());

        $bind = array();
        $like = array();
        $where = '';
        $likeCond = '';
        if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE
            || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
        ) {
            $helper = Mage::getResourceHelper('core');
            $words = Mage::helper('core/string')->splitWords($queryText, true, $query->getMaxQueryWords());
            foreach ($words as $word) {
                $like[] = $helper->getCILike('s.data_index', $word, array('position' => 'any'));
            }
            if ($like) {
                $likeCond = '(' . join(' OR ', $like) . ')';
            }
        }

        $mainTableAlias = 's';
        $fields = array(
            'post_id'
        );
        $select = $adapter->select()
            ->from(array($mainTableAlias => $this->getMainTable()), $fields)
            ->joinInner(array('e' => $this->getTable('evozon_blog/post')),
                'e.entity_id = s.post_id',
                array())
            ->where($mainTableAlias . '.store_id = ?', (int)$query->getStoreId());

        if ($searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_FULLTEXT
            || $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE
        ) {
            $bind[':query'] = implode(' ', $preparedTerms[0]);
            $where = $this->_chooseFulltext($mainTableAlias, $select, $bind[':query']);
        }

        if ($likeCond != '' && $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_COMBINE) {
            $where .= ($where ? ' OR ' : '') . $likeCond;
        } elseif ($likeCond != '' && $searchType == Mage_CatalogSearch_Model_Fulltext::SEARCH_TYPE_LIKE) {
            $select->columns(array('relevance' => new Zend_Db_Expr(0)));
            $where = $likeCond;
        }

        if ($where != '') {
            $select->where($where);
        }

        $postIds = $this->_getReadAdapter()->fetchCol($select, $bind);
        return $postIds;
    }

     /**
     * Join information for using full text search
     * 
     * @param  string $alias
     * @param  Varien_Db_Select $select
     * @param  string $query
     *
     * @return Varien_Db_Select $select
     */
    protected function _chooseFulltext( $alias, $select, $query)
    {
        $field = new Zend_Db_Expr('MATCH (' . $alias . '.data_index) AGAINST (' . $query . ' IN BOOLEAN MODE)');
        $select->columns(array('relevance' => $field));

        return $field;
    }

    /**
     * Retrieve searchable attributes
     *
     * @param string $backendType
     *
     * @return array
     */
    protected function _getSearchableAttributes($backendType = null)
    {
        if (is_null($this->_searchableAttributes)) {
            $this->_searchableAttributes = array();

            $postAttributeCollection = Mage::getResourceModel('evozon_blog/post_attribute_collection');

            if ($this->_engine && $this->_engine->allowAdvancedIndex()) {
                $postAttributeCollection->addToIndexFilter(true);
            } else {
                $postAttributeCollection->addSearchableAttributeFilter();
            }
            $attributes = $postAttributeCollection->getItems();

            $entity = $this->getEavConfig()
                ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
                ->getEntity();

            foreach ($attributes as $attribute) {
                $attribute->setEntity($entity);
            }

            $this->_searchableAttributes = $attributes;
        }

        if (!is_null($backendType)) {
            $attributes = array();
            foreach ($this->_searchableAttributes as $attributeId => $attribute) {
                if ($attribute->getBackendType() == $backendType) {
                    $attributes[$attributeId] = $attribute;
                }
            }

            return $attributes;
        }

        return $this->_searchableAttributes;
    }

    /**
     * Retrieve searchable attribute by Id or code
     *
     * @param int|string $attribute
     *
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function _getSearchableAttribute($attribute)
    {
        $attributes = $this->_getSearchableAttributes();
        if (is_numeric($attribute)) {
            if (isset($attributes[$attribute])) {
                return $attributes[$attribute];
            }
        } elseif (is_string($attribute)) {
            foreach ($attributes as $attributeModel) {
                if ($attributeModel->getAttributeCode() == $attribute) {
                    return $attributeModel;
                }
            }
        }

        return $this->getEavConfig()->getAttribute(Evozon_Blog_Model_Post::ENTITY, $attribute);
    }

    /**
     * Load product(s) attributes
     *
     * @param int $storeId
     * @param array $postIds
     * @param array $attributeTypes
     * @return array
     */
    protected function _getPostAttributes($storeId, array $postIds, array $attributeTypes)
    {
        $result = array();
        $selects = array();
        $adapter = $this->_getWriteAdapter();
        $ifStoreValue = $adapter->getCheckSql('t_store.value_id > 0', 't_store.value', 't_default.value');
        foreach ($attributeTypes as $backendType => $attributeIds) {
            if ($attributeIds) {
                $tableName = $this->getTable(array('evozon_blog/post', $backendType));
                $select = $adapter->select()
                    ->from(
                        array('t_default' => $tableName),
                        array('entity_id', 'attribute_id'))
                    ->joinLeft(
                        array('t_store' => $tableName),
                        $adapter->quoteInto(
                            't_default.entity_id=t_store.entity_id' .
                            ' AND t_default.attribute_id=t_store.attribute_id' .
                            ' AND t_store.store_id=?',
                            $storeId),
                        array('value' => $this->_unifyField($ifStoreValue, $backendType)))
                    ->where('t_default.store_id=?', 0)
                    ->where('t_default.attribute_id IN (?)', $attributeIds)
                    ->where('t_default.entity_id IN (?)', $postIds);

                $selects[] = $select;
            }
        }

        if ($selects) {
            $select = $adapter->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
            $query = $adapter->query($select);
            while ($row = $query->fetch()) {
                $result[$row['entity_id']][$row['attribute_id']] = $row['value'];
            }
        }

        return $result;
    }

    /**
     * Retrieve Post Emulator (Varien Object)
     *
     * @return Varien_Object
     */
    protected function _getPostEmulator()
    {
        $postEmulator = new Varien_Object();
        $postEmulator->setIdFieldName('entity_id');

        return $postEmulator;
    }

    /**
     * Prepare Fulltext index value for product
     *
     * @param array $indexData
     * @param array $postData
     * @param int $storeId
     *
     * @return string
     */
    protected function _preparePostIndex($indexData, $postData, $storeId)
    {
        $index = array();

        foreach ($this->_getSearchableAttributes('static') as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (!isset($postData[$attributeCode])) {
                continue;
            }

            $value = $this->_getAttributeValue($attribute->getId(), $postData[$attributeCode], $storeId);
            if ($value) {
                $index[$attributeCode] = $value;
            }
        }

        foreach ($indexData as $entityId => $attributeData) {
            foreach ($attributeData as $attributeId => $attributeValue) {
                $value = $this->_getAttributeValue($attributeId, $attributeValue, $storeId);
                if (!is_null($value) && $value !== false) {
                    $attributeCode = $this->_getSearchableAttribute($attributeId)->getAttributeCode();

                    if (isset($index[$attributeCode])) {
                        $index[$attributeCode][$entityId] = $value;
                    } else {
                        $index[$attributeCode] = array($entityId => $value);
                    }
                }
            }
        }

        if ($this->_engine) {
            return $this->_engine->prepareEntityIndex($index, $this->_separator);
        }

        return Mage::helper('catalogsearch')->prepareIndexdata($index, $this->_separator);
    }

    /**
     * Save Post index
     *
     * @param int $postId
     * @param int $storeId
     * @param string $index
     *
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    protected function _savePostIndex($postId, $storeId, $index)
    {
        return parent::_saveProductIndex($postId, $storeId, $index);
    }

    /**
     * Save Multiply Product indexes
     *
     * @param int $storeId
     * @param array $postIndexes
     *
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    protected function _savePostIndexes($storeId, $postIndexes)
    {
        return parent::_saveProductIndexes($storeId, $postIndexes);
    }

}
