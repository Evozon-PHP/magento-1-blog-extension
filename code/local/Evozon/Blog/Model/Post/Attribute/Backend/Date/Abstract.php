<?php

/**
 * Abstract backend model for date
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Backend_Date_Abstract extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{

    /**
     * Convert create date from UTC to current store time zone
     *
     * @param Varien_Object $object
     * @return Evozon_Blog_Model_Post_Attribute_Backend_UpdatedAt
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $date = $object->getData($attributeCode);

        if (is_null($date)) {
            return $this;
        }

        $zendDate = Mage::app()->getLocale()->storeDate(null, $date, true,
            $this->_getFormat($date));

        // set default format from system config
        $format = Zend_Locale_Format::convertPhpToIsoFormat(
                Mage::getSingleton('evozon_blog/config')->getGeneralConfig(Evozon_Blog_Model_Config_General::DATETIME_DEFAULT_FORMAT)
        );

        if (Mage::app()->getStore()->isAdmin()) {
            $format = $this->_getFormat($date);
        }

        $object->setData($attributeCode, $zendDate->toString($format));

        return $this;
    }

    /**
     * Returns date format if it matches a certain mask
     *
     * @param $date
     * @return null|string
     */
    protected function _getFormat($date)
    {
        if (is_string($date) && preg_match('#^\d{4,4}-\d{2,2}-\d{2,2} \d{2,2}:\d{2,2}:\d{2,2}$#',
                $date)) {
            return 'yyyy-MM-dd HH:mm:ss';
        }

        return null;
    }

}
