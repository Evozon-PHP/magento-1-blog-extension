<?php
 
/**
 * Set the backend model for date attributes
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */

$installer = $this;
 
/* @var $installer Evozon_Blog_Model_Resource_Setup */
$installer->startSetup();
 
// update the post attributes
$installer->updateAttribute(Evozon_Blog_Model_Post::ENTITY, 'publish_date', 'backend_model', 'evozon_blog/post_attribute_backend_date_publishedAt');
$installer->updateAttribute(Evozon_Blog_Model_Post::ENTITY, 'created_at', 'backend_model', 'evozon_blog/post_attribute_backend_date_createdAt');
$installer->updateAttribute(Evozon_Blog_Model_Post::ENTITY, 'updated_at', 'backend_model', 'evozon_blog/post_attribute_backend_date_updatedAt');

// update the tag attribute
$installer->updateAttribute(Evozon_Blog_Model_Tag::ENTITY, 'created_at', 'backend_model', 'evozon_blog/post_attribute_backend_date_createdAt');
 
$installer->endSetup();