<?php

/**
 * Upgrade script to create schedule data (will contain posts with pending status)
 * This table is for the cron job which change the post status in published at the scheduled date
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
$installer = $this;
$installer->startSetup();

$table = $this->getConnection()

    // create schedule data table
    ->newTable($this->getTable('evozon_blog/scheduler'))

    // add primary field
    ->addColumn(
        'id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, 
        10, 
        array(
            'unsigned' => true,
            'identity' => true,
            'nullable' => false,
            'primary' => true,
        ), 
        'ID: primary and autoincrement'
    )
    
    // add post_id field
    ->addColumn(
        'post_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER, 
        10, 
        array(
            'unsigned' => true,
            'nullable' => false
        ), 
        'Post ID'
    )

    // add post status
    ->addColumn(
        'next_status',
        Varien_Db_Ddl_Table::TYPE_SMALLINT, 
        2, 
        array(
            'nullable' => false,
            'unsigned' => true
        ), 
        'Post statuses: 1->published, 2->pending, 3->draft, 4->archived'
    )
    
    // time (when the post has been set to be published)
    ->addColumn(
        'time', 
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP, 
        null, 
        array(
            'nullable' => false
        ), 
        'Post published date'
    )
    
    ->setComment('Posts that must be automatic published');

$this->getConnection()->createTable($table);

// add constraint: if post will be deleted, delete also scheduled entities
$installer->getConnection()->addConstraint(
    'FK_SCHEDULER_POST_ID', $installer->getTable('evozon_blog/scheduler'), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', 'cascade', 'cascade'
);

$this->endSetup();