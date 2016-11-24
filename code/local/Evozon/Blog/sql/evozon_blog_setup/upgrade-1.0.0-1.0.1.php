<?php

/**
 * Install comments table
 *
 * @package     Evozon_Blog
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
$installer = $this;
$installer->startSetup();

/*
 * create evozon_blog_comments table. 
 * this will hold all the comments that has been added to a post
 */
$comments = $installer->getConnection()
    ->newTable($installer->getTable('evozon_blog/comment'))

    // id: primary, unsigned, not null, integer, autoincrement
    ->addColumn(
        'id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true
        ), 'Entity ID of the comment: primary and autoincrement'
    )

    // post_id: the post of the comment, unsigned, not null, integer
    ->addColumn(
        'post_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => true,
        'nullable' => false,
        ), 'Post id to which the comment has been added.'
    )

    // store_id: smallint, not null, default 0, unsigned
    ->addColumn(
        'store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'nullable' => false,
        'default' => 0,
        'unsigned' => true,
        ), 'The store_id this comment has been added to ... '
    )

    // author: the author of the comment, not null, varchar
    ->addColumn(
        'author', Varien_Db_Ddl_Table::TYPE_VARCHAR, 200, array(
        'nullable' => false,
        ), 'Author name, added as string.'
    )

    // author_email: the author's email, varchar, not null
    ->addColumn(
        'author_email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable' => false,
        ), 'Author\'s email.'
    )

    // author_ip: the author's ip, varchar, not null
    ->addColumn(
        'author_ip', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable' => false,
        ), 'Author\'s ip.'
    )

    // created_at: when the comment has been created, sql timestamp, not null
    ->addColumn(
        'created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => false,
        'default' => 'CURRENT_TIMESTAMP',
        )
    )

    // modified_at: when the comment has been modified, sql timestamp, null
    ->addColumn(
        'modified_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable' => true,
        'default' => null,
        )
    )

    // subject: unsigned, null, varchar 255
    ->addColumn(
        'subject', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        ), 'Subject of the comment'
    )

    // content: the content of the comment, text, not null
    ->addColumn(
        'content', Varien_Db_Ddl_Table::TYPE_TEXT, NULL, array(
        'nullable' => false
        ), 'Content of the message added by user.'
    )

    // approved: whether the comment has been approved or not, text, not null
    ->addColumn(
        'status', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
        'nullable' => false,
        'default' => 0,
        'unsigned' => true,
        ), '0 -> pending, 1 -> approved, 2 -> rejected, 3 -> spam'
    )
    //when the approved status is changed the comment will be disabled
    ->addColumn(
        'enabled', Varien_Db_Ddl_Table::TYPE_SMALLINT, 1, array(
        'nullable' => false,
        'default' => 0,
        'unsigned' => true
        ), '1 - enabled or 0 - disabled'
    )
    // user_id: frontend user id that created the comment, int, not null, unsigned
    ->addColumn(
        'user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable' => false,
        'unsigned' => true,
        'default' => 0
        ), 'Author id of the comment, if the customer is logged in when added the comment'
    )

    // admin_id: backend user id that created the comment, int, not null, unsigned
    ->addColumn(
        'admin_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable' => false,
        'unsigned' => true,
        'default' => 0
        ), 'Author id of the comment, if the admin has created a new comment/replied to a comment'
    )

    // parent_id: reference to other comment, int, not null, unsigned
    ->addColumn(
        'parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'nullable' => false,
        'unsigned' => true,
        'default' => 0
        ), 'Implement subcomments for the comments.'
    )

    // add the tree path of the comment - the ids of the parent comments
    ->addColumn(
        'path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        'unsigned' => false,
        'identity' => false,
        'nullable' => false,
        'primary' => false
        ), 'Tree Path'
    )

    // add the tree level column
    ->addColumn(
        'level', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
        'unsigned' => false,
        'identity' => false,
        'nullable' => false,
        'primary' => false,
        'default' => 0
        ), 'Tree Level'
    )

    // add post_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/comment', array('post_id')), array('post_id')
    )

    // add store_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/comment', array('store_id')), array('store_id')
    )

    // add admin_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/comment', array('admin_id')), array('admin_id')
    )

    // add user_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/comment', array('user_id')), array('user_id')
    )

    // add parent_id index.
    ->addIndex(
        $installer->getIdxName('evozon_blog/comment', array('parent_id')), array('parent_id')
    )

    // add created_at index.
    ->addIndex(
    $installer->getIdxName('evozon_blog/comment', array('created_at')), array('created_at')
);
$installer->getConnection()->createTable($comments);

// add constraint: if post will be deleted, delete also comments.
$installer->getConnection()->addConstraint(
    'FK_POST_ID', $installer->getTable('evozon_blog/comment'), 'post_id', $installer->getTable('evozon_blog/post'), 'entity_id', 'cascade', 'cascade'
);

$installer->endSetup();
