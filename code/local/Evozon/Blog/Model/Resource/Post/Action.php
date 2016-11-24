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
class Evozon_Blog_Model_Resource_Post_Action extends Evozon_Blog_Model_Resource_Abstract
{

    /**
     * Intialize connection
     *
     */
    protected function _construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType(Evozon_Blog_Model_Post::ENTITY)
            ->setConnection(
                $resource->getConnection('core_read'), $resource->getConnection('core_write')
        );
    }

    /**
     * Update attribute values for entity list per store
     * Because we are calling it from the resource, the reindex won`t be triggered
     *
     * @param array $entityIds
     * @param array $attrData
     * @param int $storeId
     * @return Evozon_Blog_Model_Resource_Post_Action
     */
    public function updateAttribute($entityIds, $attrData, $storeId)
    {
        $object = new Varien_Object();
        $object->setIdFieldName('entity_id')
            ->setStoreId($storeId);

        $this->_getWriteAdapter()->beginTransaction();
        try {
            $attribute = $this->getAttribute(key($attrData));
            $value = $attrData[key($attrData)];

            $i = 0;
            foreach ($entityIds as $entityId) {
                $i++;
                $object->setId($entityId);
                $this->_saveAttributeValue($object, $attribute, $value);
                if ($i % 100 == 0) {
                    $this->_processAttributeValues();
                }
            }
            $this->_processAttributeValues();

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }
}
