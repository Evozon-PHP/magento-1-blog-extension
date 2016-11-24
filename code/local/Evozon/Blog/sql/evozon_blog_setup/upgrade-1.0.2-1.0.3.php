<?php
/**
 * Install restrictions table
 *
 * @package     Evozon_Blog
 * @copyright   Copyright (c) 2015, Evozon
 * @author      Denis-Florin Rendler <denis.rendler@evozon.com>
 * @link        http://www.evozon.com  Evozon
 */
$installer = $this;
$installer->startSetup();

/**
 * Create evozon_blog_restrictions table.
 * this will hold all the restrictions that has been added to a post
 */
$restrictionsTable = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/restriction'))

    // id: primary, unsigned, not null, integer, autoincrement
    ->addColumn(
        'restriction_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
            'identity' => true
        ),
        'Entity ID of the restriction: primary and autoincrement'
    )

    // post_id: the post for which the restriction was created, unsigned, not null, integer
    ->addColumn(
        'post_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        10,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Post id to which the restriction has been added.'
    )

    // restrictions_serialized: the serialized restrictions, not null, varchar
    ->addColumn(
        'restrictions_serialized',
        Varien_Db_Ddl_Table::TYPE_BLOB,
        '2M',
        array(
            'nullable' => false,
        ),
        'Author name, added as string.'
    )

    // created_at: when the restriction was created, sql timestamp, not null
    ->addColumn(
        'created_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => true,
            'default' => null,
        )
    )

    // modified_at: when the comment has been modified, sql timestamp, null
    ->addColumn(
        'updated_at',
        Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
        null,
        array(
            'nullable' => true,
            'default' => null,
        )
    )

    // add entity_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/restriction', array('restriction_id')), array('restriction_id')
    )

    // add post_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/restriction', array('post_id')), array('post_id')
    );

$installer->getConnection()->createTable($restrictionsTable);

// add constraint: if post will be deleted, delete also restrictions.
$installer
    ->getConnection()
    ->addConstraint(
        'FK_RESTRICTION_POST_ID_POST_ENTITY_ENTITY_ID',
        $installer->getTable('evozon_blog/restriction'),
        'post_id',
        $installer->getTable('evozon_blog/post'),
        'entity_id',
        'cascade',
        'cascade'
    );

$installer->endSetup();