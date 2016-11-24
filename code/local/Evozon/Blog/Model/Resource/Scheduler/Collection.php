<?php

/**
 * Scheduled date collection
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Resource_Scheduler_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/scheduler');
    }
}
