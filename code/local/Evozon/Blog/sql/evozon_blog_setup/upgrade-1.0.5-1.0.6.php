<?php

/**
* Create url_structure attribute
*
* @package     Evozon_
* @author      Szegedi Szilard <szilard.szegedi@evozon.com>
* @copyright   Copyright (c) 2015, Evozon
* @link        http://www.evozon.com  Evozon
*/

$installer = $this;

/* @var $installer Evozon_Blog_Model_Resource_Setup */
$installer->startSetup();

$fulltext = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/search_fulltext'))
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true
    ), 'ID')

    // post_id: the post of the comment, unsigned, not null, integer
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Post ID')

    // store_id: smallint, not null, default 0, unsigned
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'nullable' => false,
        'default' => 0,
        'unsigned' => true,
    ), 'Store ID ')

    // author: the author of the comment, not null, varchar
    ->addColumn(
        'data_index', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable' => false,
        'length' => '4g'
    ), 'Data index.')
    ->setOption('type', 'MyISAM');

$installer->getConnection()->createTable($fulltext);

$installer->getConnection()->addIndex(
    $installer->getTable('evozon_blog/search_fulltext'),
    $installer->getIdxName(
        'evozon_blog/search_fulltext',
        array('data_index'),
        Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT
    ),
    array('data_index'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_FULLTEXT
);

$installer->endSetup();
