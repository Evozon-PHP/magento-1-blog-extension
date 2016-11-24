<?php

/**
 * Instead of a text field, there will be a checkbox for "Show all" that will disable the text field when checked
 * It is used in system_config, in every field we have to set a limit :)
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_Limit extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Overwrite parent method in order to add the checkbox and the specific events
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return String
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $element->setStyle('width:200px');
        $afterHtmlElement = parent::_getElementHtml($element);
        $afterHtmlElement .= $this->setTemplate('evozon/blog/system/form/limit.phtml')
            ->setTextField($afterHtmlElement)
            ->setElementId($element->getHtmlId())
            ->toHtml();

        return $afterHtmlElement;
    }
}