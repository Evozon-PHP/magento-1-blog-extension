<?php

/**
 * Post media gallery attribute backend resource
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Resource_Post_Attribute_Backend_Media extends Mage_Catalog_Model_Resource_Product_Attribute_Backend_Media
{
    /**
     * Post image table
     */
    const GALLERY_TABLE = 'evozon_blog/post_image';
    
    /**
     * Post image value table 
     * (contains properties that can be changed depending on store)
     */
    const GALLERY_VALUE_TABLE = 'evozon_blog/post_image_value';
    
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init(self::GALLERY_TABLE, 'value_id');
    }
    
    /**
     * Load gallery images for post using reusable select method
     *
     * @param Evozon_Blog_Model_Post $post
     * @param Evozon_Blog_Model_Resource_Post_Attribute_Backend_Media $object
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function loadGallery($post, $object)
    {
        $postIds = array($post->getId());
        
        $select = $this->_getLoadGallerySelect($postIds, $post->getStoreId(), $object->getAttribute()->getId());

        $adapter = $this->_getReadAdapter();
        $result = $adapter->fetchAll($select);
        $this->_removeDuplicates($result);
        
        return $result;
    }
    
    /**
     * Get select to retrieve media gallery images for given post ids
     *
     * @param array $postIds
     * @param $storeId
     * @param int $attributeId
     * @return Varien_Db_Select
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getLoadGallerySelect(array $postIds, $storeId, $attributeId) 
    {
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('value.position IS NULL', 'default_value.position', 'value.position');
        
        // Select gallery images for post
        $select = $adapter->select()
            ->from(
                array('main'=>$this->getMainTable()),
                array('value_id', 'value AS file', 'post_id' => 'entity_id')
            )
            ->joinLeft(
                array('value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                $adapter->quoteInto('main.value_id = value.value_id AND value.store_id = ?', (int)$storeId),
                array('label','href','position','disabled')
            )
            ->joinLeft( // Joining default values
                array('default_value' => $this->getTable(self::GALLERY_VALUE_TABLE)),
                'main.value_id = default_value.value_id AND default_value.store_id = 0',
                array(
                    'label_default' => 'label',
                    'href_default' => 'href',
                    'position_default' => 'position',
                    'disabled_default' => 'disabled'
                )
            )
            ->where('main.attribute_id = ?', $attributeId)
            ->where('main.entity_id in (?)', $postIds)
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }

    /**
     * Insert gallery value for store to db
     *
     * @param array $data
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Backend_Media
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function insertGalleryValueInStore($data)
    {        
        $data = $this->_prepareDataForTable(new Varien_Object($data), $this->getTable(self::GALLERY_VALUE_TABLE));
        $this->_getWriteAdapter()->insert($this->getTable(self::GALLERY_VALUE_TABLE), $data);

        return $this;
    }

    /**
     * Delete gallery value for store in db
     *
     * @param integer $valueId
     * @param integer $storeId
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Backend_Media
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function deleteGalleryValueInStore($valueId, $storeId)
    {
        $adapter = $this->_getWriteAdapter();

        $conditions[] = $adapter->quoteInto('value_id = ?', (int) $valueId);
        $conditions[] = $adapter->quoteInto('store_id = ?', (int) $storeId);
        
        $adapter->delete($this->getTable(self::GALLERY_VALUE_TABLE), $conditions);

        return $this;
    }
}
