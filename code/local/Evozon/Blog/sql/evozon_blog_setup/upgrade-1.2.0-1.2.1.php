<?php
 /**
 * Add new column on comment table to handle if is checked or not to send
 * notification email to the user that wrote that comment
  *
 * @package     Evozon_Blog
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   Copyright (c) 2017, Evozon
 * @link        http://www.evozon.com  Evozon
 */

/**
 * Install comments table
 *
 * @package     Evozon_Blog
 * @copyright   Copyright (c) 2017, Evozon
 * @link        http://www.evozon.com  Evozon
 */
$installer = $this;
$installer->startSetup();

$comments = $installer->getConnection()
    ->addColumn($installer->getTable('evozon_blog/comment'),
        'notify_customer', array(
            'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
            'default'   => true,
            'nullable'  => false,
            'after'     => 'status',
            'comment'   => 'Notify customer when someone replies on comment '
        )
    );
$installer->endSetup();