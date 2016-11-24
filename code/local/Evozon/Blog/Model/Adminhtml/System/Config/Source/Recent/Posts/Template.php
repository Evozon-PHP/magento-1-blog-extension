<?php

/**
 * Model for managing widget template and settings
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Recent_Posts_Template
{
    
    /**
     * Getting the options for the available templates
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'evozon/blog/post/list/recent/posts/minimal.phtml', 'label' => Mage::helper('evozon_blog')->__('Minimal')),
            array('value' => 'evozon/blog/post/list/recent/posts/extended.phtml', 'label' => Mage::helper('evozon_blog')->__('Extended')),
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
