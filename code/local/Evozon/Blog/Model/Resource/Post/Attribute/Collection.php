<?php
/**
 * Post attribute collection model
 *
 * @category    Evozon
 * @package     Evozon_Blog
 */
class Evozon_Blog_Model_Resource_Post_Attribute_Collection extends Mage_Eav_Model_Resource_Entity_Attribute_Collection
{
    /**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/resource_eav_attribute', 'eav/entity_attribute');
    }
    
    /**
     * Init attribute select
     *
     * @access protected
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Collection
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()))
            ->where(
                'main_table.entity_type_id=?',
                Mage::getModel('eav/entity')->setType(Evozon_Blog_Model_Resource_Post_Collection::ENTITY)->getTypeId()
            )
            ->join(
                array('additional_table' => $this->getTable('evozon_blog/eav_attribute')),
                'additional_table.attribute_id=main_table.attribute_id'
            );
        return $this;
    }

    /**
     * Set entity type filter
     *
     * @access public
     * @param string $typeId
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Collection
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }

    /**
     * Specify filter by "is_visible" field
     *
     * @access public
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Collection
     */
    public function addVisibleFilter()
    {
        return $this->addFieldToFilter('additional_table.is_visible', 1);
    }

    /**
     * Specify filter by "is_editable" field
     *
     * @access public
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Collection
     */
    public function addEditableFilter()
    {
        return $this->addFieldToFilter('additional_table.is_editable', 1);
    }

    /**
     * Specify filter for attributes used in quick search
     *
     * @return Evozon_Blog_Model_Resource_Eav_Mysql4_Post_Attribute_Collection
     */
    public function addSearchableAttributeFilter()
    {
        $this->getSelect()->where(
            'additional_table.is_searchable = 1 OR '.
            $this->getConnection()->quoteInto('main_table.attribute_code IN (?)', array('status', 'visibility'))
        );

        return $this;
    }
}
