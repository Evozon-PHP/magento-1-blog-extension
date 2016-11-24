<?php

/**
 * Abstract class for crons
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
abstract class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Cron_Abstract extends Mage_Core_Model_Config_Data
{
    /**
     * Config model
     */
    protected $_config = null;
    
    /**
     * Get config model
     * 
     * @return Evozon_Blog_Model_Cron_Config
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getConfig()
    {
        if (!$this->_config) {
            $this->_config = Mage::getSingleton('evozon_blog/cron_config');
        }
        
        return $this->_config;
    }
}