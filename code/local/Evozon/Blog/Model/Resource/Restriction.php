<?php

/**
 * Restriction resource model
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Restriction
    extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/restriction', 'restriction_id');
    }
}