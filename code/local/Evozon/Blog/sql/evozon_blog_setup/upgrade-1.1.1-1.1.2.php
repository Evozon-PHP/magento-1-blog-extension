<?php

/**
 * Add an attribute to select which posts will be visible for archive
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */

$this->startSetup();

$attCode = 'archive_status';
$attributeId = Mage::getModel('eav/entity_attribute')->getIdByCode('evozon_blog_post', $attCode);
if (!$attributeId) {
    $this->addAttribute(Evozon_Blog_Model_Post::ENTITY, $attCode, array(
        'type' => 'int',
        'label' => 'Visible in archive',
        'input' => 'select',
        'source' => 'eav/entity_attribute_source_boolean',
        'sort_order' => 9,
        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'required' => true,
        'default' => 1,
        'note' => Mage::helper('evozon_blog')->__("Allow this post to appear in archive filtering."),
        'group' => 'General'
    ));
}
 
$this->endSetup();