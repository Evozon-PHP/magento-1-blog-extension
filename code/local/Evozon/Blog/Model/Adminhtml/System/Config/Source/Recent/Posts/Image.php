<?php

/**
 * Model for managing widget image style
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Recent_Posts_Image
{

    /**
     * Getting the options for the available image styles
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label' => Mage::helper('evozon_blog')->__('Big Image')),
            array('value' => 1, 'label' => Mage::helper('evozon_blog')->__('Thumbnail')),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function toArray()
    {
        $helper = Mage::helper('evozon_blog');
        $labels = $helper->arrayColumn($this->toOptionArray(), 'label');
        $keys = $helper->arrayColumn($this->toOptionArray(), 'value');
        
        return array_combine($keys, $labels);
    }
}
