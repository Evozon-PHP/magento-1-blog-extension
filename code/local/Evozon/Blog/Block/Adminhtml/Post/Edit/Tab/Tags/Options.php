<?php

/**
 * Helper block to display the adding a new tag form
 * in the Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Tags block
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Tags_Options extends Evozon_Blog_Block_Adminhtml_Tag_Edit_Tab_Options
{    
    /**
     * Retrieve frontend labels of attribute for each store
     *
     * @return array
     */
    protected function getTagValues()
    {
        $values = array();

        foreach ($this->getStores() as $store) {
            $values[$store->getId()]['name'] =  '';
            $values[$store->getId()]['count'] =  '0';
        }
        
        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getTagObject()
    {
        return Mage::getModel('evozon_blog/tag');
    }
}