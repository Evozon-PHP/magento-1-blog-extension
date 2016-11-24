<?php
/**
 * Resource entity for Evozon Blog attributes model Evozon_Blog_Model_Resource_Eav_Attribute
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Attribute extends Mage_Eav_Model_Resource_Entity_Attribute
{
    /**
     * catalog_product entity type id
     *
     * @var int
     */
    protected $_entityTypeId;

    /**
     * Store id
     *
     * @var int
     */
    protected $_storeId          = null;
    
    /**
     * After saving the attribute
     *
     * @access protected
     * @param Mage_Core_Model_Abstract $object
     */
    protected  function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $setup       = Mage::getModel('eav/entity_setup', 'core_write');
        $entityType  = $object->getEntityTypeId();
        $setId       = $setup->getDefaultAttributeSetId($entityType);
        $groupId     = $setup->getDefaultAttributeGroupId($entityType);
        $attributeId = $object->getId();
        $sortOrder   = $object->getPosition();

        $setup->addAttributeToGroup($entityType, $setId, $groupId, $attributeId, $sortOrder);
        
        return parent::_afterSave($object);
    }
    
    /**
     * Set store id
     *
     * @param integer $storeId
     * @return Evozon_Blog_Model_Resource_Config
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return store id.
     * If is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Retrieve catalog_product entity type id
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = Mage::getSingleton('eav/config')->getEntityType(Evozon_Blog_Model_Post::ENTITY)->getId();
        }
        return $this->_entityTypeId;
    }
    
    /**
     * Retrieve Post Attributes Used in Post listing
     *
     * @return array
     */
    public function getAttributesUsedInListing()
    {
        $adapter = $this->_getReadAdapter();
        $storeLabelExpr = $adapter->getCheckSql('al.value IS NOT NULL', 'al.value', 'main_table.frontend_label');

        $select  = $adapter->select()
            ->from(array('main_table' => $this->getTable('eav/attribute')))
            ->join(
                array('additional_table' => $this->getTable('evozon_blog/eav_attribute')),
                'main_table.attribute_id = additional_table.attribute_id'
            )
            ->joinLeft(
                array('al' => $this->getTable('eav/attribute_label')),
                'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int)$this->getStoreId(),
                array('store_label' => $storeLabelExpr)
            )
            ->where('main_table.entity_type_id = ?', (int)$this->getEntityTypeId())
            ->where('additional_table.used_in_post_listing = ?', 1);

        return $adapter->fetchAll($select);
    }
    
    /**
     * Retrieve Used Post Attributes for Blog Post Listing Sort By
     *
     * @return array
     */
    public function getAttributesUsedForSortBy()
    {
        $adapter = $this->_getReadAdapter();
        $storeLabelExpr = $adapter->getCheckSql('al.value IS NULL', 'main_table.frontend_label','al.value');
        $select = $adapter->select()
            ->from(array('main_table' => $this->getTable('eav/attribute')))
            ->join(
                array('additional_table' => $this->getTable('evozon_blog/eav_attribute')),
                'main_table.attribute_id = additional_table.attribute_id',
                array()
            )
            ->joinLeft(
                array('al' => $this->getTable('eav/attribute_label')),
                'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int)$this->getStoreId(),
                array('store_label' => $storeLabelExpr)
            )
            ->where('main_table.entity_type_id = ?', (int)$this->getEntityTypeId())
            ->where('additional_table.used_for_sort_by = ?', 1);

        return $adapter->fetchAll($select);
    }
    
    /**
     * Retrieve attribute names that belong to a specific group
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $group
     * @return array
     */
    public function getAttributeNameByGroup($group)
    {
        $adapter = $this->_getReadAdapter();
                
        $select = $adapter->select()
            ->from(array('main_table'=>$this->getTable('eav/entity_attribute')), array())
            ->join(
                array('group_table'=>$this->getTable('eav/attribute_group')),
                'group_table.attribute_group_id = main_table.attribute_group_id',
                array()
            )
            ->join(
                array('attr_table'=>$this->getTable('eav/attribute')),
                'attr_table.attribute_id = main_table.attribute_id',
                array('attribute_code')
            )
            ->where('main_table.entity_type_id = ?', (int)$this->getEntityTypeId())
            ->where('group_table.attribute_group_name = ?', $group);
        
        return $adapter->fetchCol($select);
    }
}