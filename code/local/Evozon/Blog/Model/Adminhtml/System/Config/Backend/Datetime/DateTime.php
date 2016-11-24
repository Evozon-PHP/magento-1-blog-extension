<?php

/**
 * Save DateTime in php format. Remove invalid characters
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Datetime_DateTime
    extends Evozon_Blog_Model_Adminhtml_System_Config_Backend_Datetime_Abstract
{
    /**
     * Allowed chars for date time format
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @return  array
     */
    protected function getAllowedChars()
    {
        return array('d', 'D', 'j', 'l', 'F', 'm', 'M', 'n', 'Y', 'y', 'a', 'A', 'g', 'G', 'h', 'H', 'i', 's');
    }
}
