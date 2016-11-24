<?php
 
/**
 * Upgrade script to add store_id to scghedule_data table 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */

$installer = $this;

/* @var $installer Evozon_Blog_Model_Resource_Setup */
$installer->startSetup();
 
$installer->getConnection()
     ->addColumn(
        $installer->getTable('evozon_blog/scheduler'),
        'store_id',            
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_SMALLINT,
            'length'   => 2,
            'nullable' => false,
            'unsigned'  => true,
            'comment'  => 'Post store id',
            'default'   => 0
        )
    );

$installer->getConnection()->addConstraint(
    'FK_SCHEDULER_STORE_ID', $installer->getTable('evozon_blog/scheduler'), 'store_id', $installer->getTable('core/store'), 'store_id', 'cascade', 'cascade'
);
 
$installer->endSetup();