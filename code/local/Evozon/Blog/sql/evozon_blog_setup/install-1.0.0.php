<?php

/**
 * Upgrade script to add EAV-like model to our system
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
$installer = $this;

/* @var $installer Evozon_Blog_Model_Resource_Setup */
$installer->startSetup();

/**
 * Create evozon_blog/eav_attribute table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/eav_attribute'))
    ->addColumn(
        'attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Attribute ID'
    )
    ->addColumn('frontend_input_renderer', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Frontend Input Renderer')
    ->addColumn('is_global', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '1',
        ), 'Attribute scope')
    ->addColumn('is_visible', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '1',
        ), 'Is Visible')
    ->addColumn('is_searchable', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Is Searchable')
    ->addColumn('is_visible_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Is Visible On Front')
    ->addColumn('is_html_allowed_on_front', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Is HTML Allowed On Front')
    ->addColumn('used_in_post_listing', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Is Used In Post Listing')
    ->addColumn('used_for_sort_by', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Is Used For Sorting')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '0',
        ), 'Position')
    ->addColumn('is_wysiwyg_enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute uses WYSIWYG')
    ->addIndex($installer->getIdxName('evozon_blog/eav_attribute', array('used_for_sort_by')), array('used_for_sort_by'))
    ->addForeignKey($installer->getFkName('evozon_blog/eav_attribute', 'attribute_id', 'eav/attribute', 'attribute_id'), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Blog attribute table');
$installer->getConnection()->createTable($table);

/**
 * Create table for posts (evozon_blog_post)
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/post'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Entity ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Set ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Update Time')
    ->addIndex($installer->getIdxName('evozon_blog/post', array('entity_type_id')), array('entity_type_id'))
    ->addIndex($installer->getIdxName('evozon_blog/post', array('attribute_set_id')), array('attribute_set_id'))
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post', 'attribute_set_id', 'eav/attribute_set', 'attribute_set_id'
        ), 'attribute_set_id', $installer->getTable('eav/attribute_set'), 'attribute_set_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('evozon_blog/post', 'entity_type_id', 'eav/entity_type', 'entity_type_id'), 'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Posts main table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/post', 'datetime')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/post', 'datetime')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/post', 'datetime'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'datetime'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'datetime'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'datetime'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'datetime'), 'attribute_id', 'eav/attribute', 'attribute_id'
        ), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'datetime'), 'entity_id', 'evozon_blog/post', 'entity_id'
        ), 'entity_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'datetime'), 'store_id', 'core/store', 'store_id'
        ), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Posts Datetime Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/post', 'int')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/post', 'int')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/post', 'int'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'int'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'int'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'int'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'int'), 'attribute_id', 'eav/attribute', 'attribute_id'
        ), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'int'), 'entity_id', 'evozon_blog/post', 'entity_id'
        ), 'entity_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/post', 'int'), 'store_id', 'core/store', 'store_id'
        ), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Posts Integer Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/post', 'text')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/post', 'text')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/post', 'text'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'text'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'text'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'text'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/post', 'text'), 'attribute_id', 'eav/attribute', 'attribute_id'), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/post', 'text'), 'entity_id', 'evozon_blog/post', 'entity_id'), 'entity_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName(array('evozon_blog/post', 'text'), 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Posts Text Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/post', 'varchar')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/post', 'varchar')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/post', 'varchar'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'varchar'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'varchar'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/post', 'varchar'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/post', 'varchar'), 'attribute_id', 'eav/attribute', 'attribute_id'), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/post', 'varchar'), 'entity_id', 'evozon_blog/post', 'entity_id'), 'entity_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/post', 'varchar'), 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Post Varchar Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'evozon_blog/post_website'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_website'))
    ->addColumn('post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Post ID')
    ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Website ID')
    ->addIndex($installer->getIdxName('evozon_blog/post_website', array('website_id')), array('website_id'))
    ->addForeignKey($installer->getFkName('evozon_blog/post_website', 'website_id', 'core/website', 'website_id'), 'website_id', $installer->getTable('core/website'), 'website_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('evozon_blog/post_website', 'post_id', 'evozon_blog/post', 'entity_id'), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Posts To Website Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table evozon_blog/post_product
 */
$table = $this->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_product'))
    ->addColumn(
        'rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Relation ID'
    )
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Post ID'
    )
    ->addColumn(
        'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Product ID'
    )
    ->addColumn(
        'position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '0',
        ), 'Position'
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_product', array('product_id')
        ), array('product_id')
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_product', 'post_id', 'evozon_blog/post', 'entity_id'
        ), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_product', 'product_id', 'catalog/product', 'entity_id'
        ), 'product_id', $installer->getTable('catalog/product'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_product', array('post_id', 'product_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('post_id', 'product_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Post to Product Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create the table evozon_blog/post_category
 */
$table = $this->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_category'))
    ->addColumn(
        'rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Relation ID'
    )
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Post ID'
    )
    ->addColumn(
        'category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Category ID'
    )
    ->addColumn(
        'position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '1',
        ), 'Position'
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_category', array('category_id')
        ), array('category_id')
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_category', 'post_id', 'evozon_blog/post', 'entity_id'
        ), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_category', 'category_id', 'catalog/category', 'entity_id'
        ), 'category_id', $installer->getTable('catalog/category'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_category', array('post_id', 'category_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('post_id', 'category_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Post to Category Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create the table evozon_blog/post_category
 */
$table = $this->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_related'))
    ->addColumn(
        'rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Relation ID'
    )
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Post ID'
    )
    ->addColumn(
        'related_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Related Post ID'
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_related', array('related_id')
        ), array('related_id')
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_related', 'post_id', 'evozon_blog/post', 'entity_id'
        ), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_related', 'related_id', 'evozon_blog/post', 'entity_id'
        ), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_related', array('post_id', 'related_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('post_id', 'related_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Post to other Posts Linkage Table');
$installer->getConnection()->createTable($table);

/**
 * Create table for tags (evozon_blog_tag)
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/tag'))
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Entity ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_set_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute Set ID')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Creation Time')
    ->addIndex($installer->getIdxName('evozon_blog/tag', array('entity_type_id')), array('entity_type_id'))
    ->addIndex($installer->getIdxName('evozon_blog/tag', array('attribute_set_id')), array('attribute_set_id'))
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/tag', 'attribute_set_id', 'eav/attribute_set', 'attribute_set_id'
        ), 'attribute_set_id', $installer->getTable('eav/attribute_set'), 'attribute_set_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('evozon_blog/tag', 'entity_type_id', 'eav/entity_type', 'entity_type_id'), 'entity_type_id', $installer->getTable('eav/entity_type'), 'entity_type_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Tags main table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/tag', 'int')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/tag', 'int')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/tag', 'int'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'int'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'int'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'int'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/tag', 'int'), 'attribute_id', 'eav/attribute', 'attribute_id'
        ), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/tag', 'int'), 'entity_id', 'evozon_blog/tag', 'entity_id'
        ), 'entity_id', $installer->getTable('evozon_blog/tag'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(
            array('evozon_blog/tag', 'int'), 'store_id', 'core/store', 'store_id'
        ), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Tags Integer Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table array('evozon_blog/tag', 'varchar')
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable(array('evozon_blog/tag', 'varchar')))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Value ID')
    ->addColumn('entity_type_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity Type ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Attribute ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Store ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex(
        $installer->getIdxName(
            array('evozon_blog/tag', 'varchar'), array('entity_id', 'attribute_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('entity_id', 'attribute_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'varchar'), array('attribute_id')), array('attribute_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'varchar'), array('store_id')), array('store_id'))
    ->addIndex($installer->getIdxName(array('evozon_blog/tag', 'varchar'), array('entity_id')), array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/tag', 'varchar'), 'attribute_id', 'eav/attribute', 'attribute_id'), 'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/tag', 'varchar'), 'entity_id', 'evozon_blog/tag', 'entity_id'), 'entity_id', $installer->getTable('evozon_blog/tag'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey(
        $installer->getFkName(array('evozon_blog/tag', 'varchar'), 'store_id', 'core/store', 'store_id'), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Evozon Blog Tags Varchar Attribute Backend Table');
$installer->getConnection()->createTable($table);

/**
 * Create table evozon_blog/post_tag
 */
$table = $this->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_tag'))
    ->addColumn(
        'rel_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'identity' => true,
        'nullable' => false,
        'primary' => true,
        ), 'Relation ID'
    )
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Post ID'
    )
    ->addColumn(
        'tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
        ), 'Tag ID'
    )
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable' => false,
        'default' => '0',
        ), 'Position'
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_tag', array('tag_id')
        ), array('tag_id')
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_tag', 'post_id', 'evozon_blog/post', 'entity_id'
        ), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_tag', 'tag_id', 'evozon_blog/tag', 'entity_id'
        ), 'tag_id', $installer->getTable('evozon_blog/tag'), 'entity_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_tag', 'store_id', 'core/store', 'store_id'
        ), 'store_id', $installer->getTable('core/store'), 'store_id', Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addIndex(
        $installer->getIdxName(
            'evozon_blog/post_tag', array('post_id', 'tag_id', 'store_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ), array('post_id', 'tag_id', 'store_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Post to Tag Linkage Table');
$installer->getConnection()->createTable($table);

$installer->installEntities();

$installer->endSetup();
