<?php
/**
 * Upgrade script to create attribute and tables for gallery
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'evozon_blog/post_image'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_image'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Value ID')
    ->addColumn('attribute_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Attribute ID')
    ->addColumn('entity_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Entity ID')
    ->addColumn('value', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Value')
    ->addIndex($installer->getIdxName('evozon_blog/post_image', array('attribute_id')),
        array('attribute_id'))
    ->addIndex($installer->getIdxName('evozon_blog/post_image', array('entity_id')),
        array('entity_id'))
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_image', 
            'attribute_id', 
            'eav/attribute', 
            'attribute_id'
        ), 
        'attribute_id', $installer->getTable('eav/attribute'), 'attribute_id', 
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_image', 
            'entity_id', 
            'evozon_blog/post', 
            'entity_id'
        ), 
        'entity_id', $installer->getTable('evozon_blog/post'), 'entity_id', 
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Evozon Blog Posts Image');
$installer->getConnection()->createTable($table);

/**
 * Create table 'evozon_blog/post_image_value'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/post_image_value'))
    ->addColumn('value_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Value ID')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        'default'   => '0',
        ), 'Store ID')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Label')
    ->addColumn('href', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Href')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        ), 'Position')
    ->addColumn('disabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Disabled')
    ->addIndex($installer->getIdxName('evozon_blog/post_image_value', array('store_id')),
        array('store_id'))
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_image_value',
            'value_id',
            'evozon_blog/post_image',
            'value_id'
        ),
        'value_id', $installer->getTable('evozon_blog/post_image'), 'value_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName(
            'evozon_blog/post_image_value',
            'store_id',
            'core/store',
            'store_id'
        ),
        'store_id', $installer->getTable('core/store'), 'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Evozon Blog Posts Image Value Table');
$installer->getConnection()->createTable($table);
 
$installer->endSetup();