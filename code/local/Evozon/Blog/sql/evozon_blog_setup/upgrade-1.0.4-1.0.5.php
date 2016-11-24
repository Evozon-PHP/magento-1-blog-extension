<?php

/**
 * Add attribute to catalog category: is_blog_category and show_featured_posts
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */

$this->startSetup();

// create is_blog_category attribute
$attCode = 'is_blog_category';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_category', $attCode);

// install if attribute doesn't exists
if (!$attributeId) {
    $this->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attCode, array(
        'group'         => 'Custom Design',
        'input'         => 'select',
        'type'          => 'int',
        'label'         => 'Is Blog Category?',    
        'source'        => 'eav/entity_attribute_source_boolean',
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => false,
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default'       => 0,
        'user_defined'  => true,  
        'note'          => Mage::helper('evozon_blog')->__("If you choose 'YES', no category permission rules will be applied for the Enterprise versions.")
    ));
}

// create show_featured_posts attribute
$attCode = 'show_featured_posts';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('catalog_category', $attCode);

// install if attribute doesn't exists
if (!$attributeId) {
    $this->addAttribute(Mage_Catalog_Model_Category::ENTITY, $attCode, array(
        'group' => 'Custom Design',
        'input' => 'select',
        'type' => 'int',
        'label' => 'Show Featured Blog Posts Block?',
        'source' => 'eav/entity_attribute_source_boolean',
        'visible' => true,
        'required' => false,
        'visible_on_front' => false,
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default' => 0,
        'user_defined' => true,
        'note' => Mage::helper('evozon_blog')->__("If you choose 'YES', make sure to add the proper xml node in the Custom Layout Update field. Default position is after the Products block.")
    ));
}
 
$this->endSetup();