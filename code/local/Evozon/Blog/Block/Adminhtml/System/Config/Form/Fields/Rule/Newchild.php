<?php
/**
 * Restriction rules form 'new child' control
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_Fields_Rule_Newchild
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render the html control
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('element-value-changer');
        $html = '&nbsp;<span class="rule-param rule-param-new-child"' . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>';
        $html.= '<a href="javascript:void(0)" class="label">';
        $html.= $element->getValueName();
        $html.= '</a><span class="element">';
        $html.= $element->getElementHtml();
        $html.= '</span></span>&nbsp;';

        return $html;
    }
}
