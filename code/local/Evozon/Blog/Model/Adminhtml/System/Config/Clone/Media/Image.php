<?php

/**
 * Clone model for media image placeholders configuration
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Clone_Media_Image extends Mage_Core_Model_Config_Data
{
    /**
     * The lables for post image placeholders 
     * 
     * @var array 
     */
    protected $labelsArray = array(
        'image' => 'Post View Image', 
        'small_image' => 'Listing Image', 
        'thumbnail' => 'Thumbnail'
    );
    
    /**
     * Get fields prefixes
     *
     * @return array
     */
    public function getPrefixes()
    {
        // use cached eav config
        $entityTypeId = Mage::getSingleton('eav/config')->getEntityType(Evozon_Blog_Model_Post::ENTITY)->getId();

        /* @var $collection Evozon_Blog_Model_Resource_Post_Attribute_Collection */
        $collection = Mage::getResourceModel('evozon_blog/post_attribute_collection');
        $collection->setEntityTypeFilter($entityTypeId);
        $collection->setFrontendInputTypeFilter('media_image');

        $prefixes = array();
        
        foreach ($collection as $attribute) {            
            /* @var $attribute Evozon_Blog_Model_Resource_Eav_Attribute */
            $prefixes[] = array(
                'field' => $attribute->getAttributeCode() . '_',
                'label' => Mage::helper('evozon_blog')->__($this->labelsArray[$attribute->getAttributeCode()]),
            );
        }

        return $prefixes;
    }
}
