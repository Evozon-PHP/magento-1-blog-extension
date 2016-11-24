<?php
/**
 * Data source collection
 * Prepares a collection on pseudo-rewrites with raw data that will be formatted by the RewriteGenerator
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Indexer_UrlRewrite_Data_Source_Collection extends Varien_Data_Collection
{
    /**
     * @var array 
     */
    protected $_postIds = array();
    
    /**
     * @var array
     */
    protected $_storeIds = array();
    
    /**
     * Get raw collection with only store ids and post ids set
     * 
     * @return \Evozon_Blog_Model_Resource_Indexer_UrlRewrite_Data_Source_Collection
     */
    public function getCollection()
    {
        foreach ($this->getPostIds() as $id) {
            foreach ($this->getStoreIds() as $storeId) {
                $object = $this->getItemById($id);
                if (!$object)
                {
                    $object = new Varien_Object();
                    $object->addData(array('entity_id' => $id, 'id' => $id ));
                }

                $object->addData(array('store_id' => $storeId));
                $object->setId("{$id}-$storeId");
                $this->addItem($object);
            }
        }

        return $this;
    }

    /**
     * Adding attribute value to collection items
     * 
     * @param string $attrCode
     * @param array $attrData
     */
    public function addAttribute($attrCode, $attrData)
    {
        $groupedRowSet = array();
        $defaultValue = array();
        foreach ($attrData as $key=>$row)
        {
            $groupedRowSet[$row['entity_id']][] = $row['store_id'];

            if ($row['store_id'] == 0) {
                $defaultValue[$row['entity_id']] = $row[$attrCode];
                unset($attrData[$key]);
                continue;
            }   

            $object = $this->getItemById($row['entity_id'] . '-' . $row['store_id']);
            $object->addData(array($attrCode=>$row[$attrCode]));
        }

        foreach ($groupedRowSet as $gEntityId => $gStoreIds)
        {                
            $storeDiff = array_diff($this->getStoreIds(), $gStoreIds);
            foreach($storeDiff as $dStoreId) {
                $object = $this->getItemById($gEntityId . '-' . $dStoreId);
                $object->addData(array($attrCode=>$defaultValue[$gEntityId]));
            }
        } 
        
        return $this;
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
