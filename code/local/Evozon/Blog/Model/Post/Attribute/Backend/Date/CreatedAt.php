<?php

/**
 * Created at backend model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Backend_Date_CreatedAt extends Evozon_Blog_Model_Post_Attribute_Backend_Date_Abstract
{
    /**
     * Set created date
     * Set created date in UTC time zone
     *
     * @param Mage_Core_Model_Object $object
     * @return Evozon_Blog_Model_Post_Attribute_Backend_Date_CreatedAt
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $date = $object->getData($attributeCode);
        
        if (is_null($date)) {
            if ($object->isObjectNew()) {
                $object->setData($attributeCode, now());
            }
        } else {
            // convert to UTC
            $zendDate = Mage::app()->getLocale()->utcDate(null, $date, true, $this->_getFormat($date));
            $object->setData($attributeCode, $zendDate->getIso());
        }

        return $this;
    }
}
