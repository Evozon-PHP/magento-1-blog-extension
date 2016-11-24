<?php

/**
 * Abstract for save date and time in php format. Remove invalid characters
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Datetime_Abstract 
    extends Mage_Core_Model_Config_Data 
{
    
    /**
     * clean invalid characters from datetime format and save again.
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @return  \Evozon_Blog_Model_Adminhtml_System_Config_Backend_DateTime_Abstract
     */
    protected function _afterSave() 
    {
        // call the parent method.
        parent::_afterSave();
        
        // allowed time and date characters
        $allowedChars = $this->getAllowedChars();
        $dateTimeConfig = $this->getData('value');
        
        // clean characters
        $characters = Mage::helper('evozon_blog')->keepOnlyAllowedChararacters($allowedChars, $dateTimeConfig);
        
        // save data
        $configModel = new Mage_Core_Model_Config();
        $configModel->saveConfig($this->getPath(), $characters);
                           
        return $this;
    }
    
    /**
     * method will be implemented in child class,
     * will return array of allowed date format chars.
     */
    protected abstract function getAllowedChars();
}
