<?php

/**
 * Save Date in php format. Remove invalid characters
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Datetime_Date 
    extends Evozon_Blog_Model_Adminhtml_System_Config_Backend_Datetime_Abstract
{
    /**
     * Allowed chars for date format
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @return  array
     */
    protected function getAllowedChars()
    {
        return array('M', 'm', 'F', 'Y', 'y', 'd', 'D', 'j', 'l', 'N', 'S', 'w', 'z', 'n', 'o');
    }
   
}
