<?php

/**
 * Update attributes
 * Ex: used in mass actions from Evozon_Blog_Block_Adminhtml_Post_Grid
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Post_Action extends Mage_Core_Model_Abstract
{

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/post_action');
    }

    /**
     * Update attribute values for entity list per store
     * Because we are calling it from the resource, the reindex won`t be triggered
     *
     * @DEPRECATED it will be investigated on how to use further
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return Evozon_Blog_Model_Resource_Post_Action
     */
    public function updateAttribute($entityIds, $attrData, $storeId)
    {
        Mage::dispatchEvent('evozon_blog_attribute_update_before', array(
            'attributes_data' => &$attrData,
            'post_ids'   => &$entityIds,
            'store_id'      => &$storeId
        ));

        $this->_getResource()->updateAttribute($entityIds, $attrData, $storeId);
        $this->setData(array(
            'post_ids' => array_unique($entityIds),
            'attribute' => $attrData,
            'store_id' => $storeId
        ));

        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, Evozon_Blog_Model_Post::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );

        Mage::dispatchEvent('evozon_blog_attribute_update_after', array(
            'post_ids'   => $entityIds
        ));

        return $this;
    }
}
