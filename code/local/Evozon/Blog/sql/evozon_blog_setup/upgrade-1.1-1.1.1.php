<?php

/**
 * Add attributes to post entity to be able to edit the article author
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */

$this->startSetup();

//create author_firstname
$attCode = 'author_firstname';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group'         => 'General',
        'input'         => 'text',
        'type'          => 'varchar',
        'label'         => 'First Name',
        'visible'       => true,
        'required'      => false,
        'visible_on_front' => true,
        'searchable'    => false,
        'used_in_post_listing' => true,
        'global'        => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'sort_order'    => 10,
        'user_defined'  => true
    ));
}

// create author_lastname attribute
$attCode = 'author_lastname';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group' => 'General',
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'Last Name',
        'visible' => true,
        'visible_on_front' => true,
        'required' => false,
        'sort_order'    => 11,
        'searchable' => false,
        'used_in_post_listing' => true,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'user_defined' => true
    ));
}

// create author_email attribute
$attCode = 'author_email';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'group' => 'General',
        'input' => 'text',
        'type' => 'varchar',
        'label' => 'Email',
        'visible' => true,
        'visible_on_front' => true,
        'required' => false,
        'sort_order'    => 12,
        'searchable' => false,
        'used_in_post_listing' => true,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'user_defined' => true
    ));
}
 
$this->endSetup();