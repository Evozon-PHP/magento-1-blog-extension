<?php

/**
 * Updated at backend model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Backend_Date_UpdatedAt extends Evozon_Blog_Model_Post_Attribute_Backend_Date_Abstract
{
    /**
     * Set modified date
     *
     * @param Varien_Object $object
     * @return Evozon_Blog_Model_Post_Attribute_Backend_Date_UpdatedAt
     */
    public function beforeSave($object)
    {
        $object->setData($this->getAttribute()->getAttributeCode(), now());
        
        return $this;
    }

}
