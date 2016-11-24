<?php

/**
 * Source model for layout configuration. Will list available layouts for blog in order to set the default one.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Layout extends Mage_Catalog_Model_Category_Attribute_Source_Layout
{
    /**
     * Array with option values for building the dropdown with available layouts.
     * gets rid of first 2 entries: No Layouts and Empty.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */    
    public function toOptionArray()
    {
        $layouts = $this->getAllOptions(); 
        
        array_shift($layouts);
        array_shift($layouts);
        
        return $layouts;
    }
}
