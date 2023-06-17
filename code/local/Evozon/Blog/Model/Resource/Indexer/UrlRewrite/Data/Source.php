<?php

/**
 * Data source reource used to acces db and 
 * prepare select sql and arrays with post attributes` values on each store
 * in order to prepare the new url rewrites
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Indexer_UrlRewrite_Data_Source extends Evozon_Blog_Model_Resource_Post
{
    /**
     * storing data for the attributes (id, code, table)
     * @var array
     */
    protected $_attrDefinitions = array();
    
    /**
     * post ids that require for the attributes to be fetched
     * @var array
     */
    protected $_postIds = array();
    
    /**
     * @var array
     */
    protected $_storeIds = array();
    
    /**
     * Getting all post ids (for full reindexing)
     * 
     * @return array
     */
    public function getAllPostIds()
    {
        $adapter= $this->getReadConnection();
        $select = $adapter
            ->select()
            ->from(
                array('e' => $this->getMainTable()), 
                array('entity_id' => 'entity_id')
            );
        
        return $adapter->fetchCol($select);
    }
    
    /**
     * Getting all required attributes values
     * by stores
     * in order to recreate the requested paths
     * 
     * @return array
     */
    public function getAttributesValue()
    {
        $adapter= $this->getReadConnection();
        $attributesData = $this->getAttrDefinitions();
        $storeIds = $this->getStoreIds();
        $postIds = $this->getPostIds();
        $dataSet = array();

        foreach ($this->getPostAttributesForUrl() as $attrCode) {
            $select = $adapter
                ->select()
                ->from(
                array(
                    'e' => $this->getMainTable()),
                    array('entity_id' => 'entity_id')
                );
            
            $attrId = $attributesData[$attrCode]['id'];
            $attrTable = $attributesData[$attrCode]['table'];
            $alias = Evozon_Blog_Model_Post::ENTITY . '_' . $attrCode;

            $innerCondition = array(
                $adapter->quoteInto("{$attrCode}_default.entity_id = e.entity_id", ''),
                $adapter->quoteInto("{$attrCode}_default.attribute_id = ?", $attrId),
                $adapter->quoteInto("{$attrCode}_default.store_id = ?", 0)
            );

            $joinLeftConditions = array(
                $adapter->quoteInto("{$alias}.entity_id = e.entity_id", ''),
                $adapter->quoteInto("{$alias}.attribute_id = ?", $attrId)
            );
            
            if (count($storeIds)==1)
            {
                $joinLeftConditions[] = $adapter->quoteInto("{$alias}.store_id IN(?)", $storeIds);
            }

            $select
                ->joinInner(
                    array($attrCode . '_default' => $attrTable), implode(' AND ', $innerCondition),
                    array($attrCode . '_default' => 'value')
                )
                ->joinLeft(
                    array($alias => $attrTable), implode(' AND ', $joinLeftConditions),
                    array($attrCode => 'value', $attrCode.'_store'=>'store_id')
            );

            if (count($postIds)) {
                $select->where('e.entity_id IN (?)', $postIds);
            }
            
            if (stristr($attrCode, 'date')) {
                $expr = new Zend_Db_Expr("IF (joins.{$attrCode} IS NULL, joins.{$attrCode}_default, joins.{$attrCode} ) AS {$attrCode}");
            } else {
                $expr = new Zend_Db_Expr("IF (joins.{$attrCode} IS NULL OR joins.{$attrCode} = '', joins.{$attrCode}_default, joins.{$attrCode} ) AS {$attrCode}");
            }
            
            $selectSql = $adapter->select()
                ->from(
                    array('joins' => $select),
                    array(
                        'entity_id' => 'joins.entity_id',
                        'store_id' => "joins.{$attrCode}_store",
                        $expr
                    )
                );

            $dataSet[$attrCode] = $adapter->fetchAll($selectSql);
        }
        
        return $dataSet;
    }
    
    /**
     * Initializes data that will be used [tables and type definitions]
     * 
     * @return \Evozon_Blog_Model_Resource_Indexer_DataSource
     */
    protected function getAttrDefinitions()
    {
        if (empty($this->_attrDefinitions))
        {
            $config = Mage::getModel('eav/config');
            foreach ($this->getPostAttributesForUrl() as $attrCode) {
                $attr = $config->getAttribute(self::ENTITY, $attrCode);

                $this->_attrDefinitions[$attrCode] = array();
                $this->_attrDefinitions[$attrCode]['id'] = $attr->getAttributeId();
                $this->_attrDefinitions[$attrCode]['table'] = $attr->getBackendTable();
            }
        }

        return $this->_attrDefinitions;
    }
    
    /**
     * Accessing post url instance in order to get the attributes used to create the url key
     * Used for generating url keys for each store 
     * 
     * @return array
     */
    protected function getPostAttributesForUrl()
    {
        return Mage::getSingleton('evozon_blog/factory')
                ->getPostUrlInstance()
                ->getPostAttributesForUrl();
    }
    
    public function setPostIds($postIds)
    {
        $this->_postIds = $postIds;
        return $this;
    }
    
    public function getPostIds()
    {
        return $this->_postIds;
    }
    
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }
    
    public function getStoreIds()
    {
        return $this->_storeIds;
    }
}
