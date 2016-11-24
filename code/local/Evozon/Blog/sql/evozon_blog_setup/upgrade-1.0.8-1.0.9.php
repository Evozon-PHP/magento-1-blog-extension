<?php

/**
 * Add attributes to post entity to add the customable design feature
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */

$this->startSetup();

//create use_default_design
$attCode = 'custom_use_default_settings';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group'         => 'Custom Design',
        'input'         => 'select',
        'type'          => 'int',
        'label'         => 'Use Default Post View Design',    
        'source'        => 'eav/entity_attribute_source_boolean',
        'visible'       => true,
        'required'      => true,
        'visible_on_front' => false,
        'global'        => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'default'       => 1,
        'user_defined'  => true,  
        'note'          => Mage::helper('evozon_blog')->__("If you choose 'NO', make sure to add the custom layout in the following fields.")
    ));
}

// create custom_design attribute
$attCode = 'custom_design';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group' => 'Custom Design',
        'input' => 'select',
        'type' => 'varchar',
        'label' => 'Custom Design',
        'source' => 'core/design_source_design',
        'visible' => true,
        'required' => false,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'user_defined' => true
    ));
}

// create custom_design attribute
$attCode = 'page_layout';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group' => 'Custom Design',
        'input' => 'select',
        'type' => 'varchar',
        'label' => 'Page Layout',
        'source' => 'catalog/category_attribute_source_layout',
        'visible' => true,
        'required' => false,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'user_defined' => true
    ));
}

// create custom_layout_update attribute
$attCode = 'custom_layout_update';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group' => 'Custom Design',
        'input' => 'textarea',
        'type' => 'text',
        'label' => 'Custom Layout Update',
        'backend' => 'catalog/attribute_backend_customlayoutupdate',
        'visible' => true,
        'required' => false,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'user_defined' => true
    ));
}
 
$this->endSetup();