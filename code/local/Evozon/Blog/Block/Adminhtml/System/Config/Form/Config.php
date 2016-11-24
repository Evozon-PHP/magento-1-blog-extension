<?php

/**
 * Shows helping link in system configurations, where it is called
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_Config extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Overwrite parent method in order to add onclick for element
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {   
        $afterHtmlElement = parent::_getElementHtml($element); 
        $attributes = explode('"', $afterHtmlElement);
        $afterHtmlElement .= $this->setTemplate('evozon/blog/system/config.phtml')->setAnchorUrl($this->getHelpUrl($attributes[1]))->toHtml();
              
        return $afterHtmlElement;
    }   
    
    /**
     * Gets controller action
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $anchor
     * @return string
     */
    protected function getHelpUrl($anchor)
    {
         return $this->getUrl('adminhtml/blog_system/helper').'#'.$anchor;
    }
}
