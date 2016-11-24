<?php
/**
 * Restriction rules editable from control
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_Fields_Rule_Editable
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @see Varien_Data_Form_Element_Renderer_Interface::render()
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->addClass('element-value-changer');
        $valueName = $element->getValueName();

        if ($valueName === '') {
            $valueName = '...';
        }

        if ($element->getShowAsText()) {
            $html = ' <input type="hidden" class="hidden" id="' . $element->getHtmlId()
                . '" name="' . $element->getName() . '" value="' . $element->getValue() . '"/> '
                . htmlspecialchars($valueName) . '&nbsp;';
        } else {
            $html = ' <span class="rule-param"'
                . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>'
                . '<a href="javascript:void(0)" class="label">';

            $translate = Mage::getSingleton('core/translate_inline');

            $html .= $translate->isAllowed() ? Mage::helper('core')->escapeHtml($valueName) :
                Mage::helper('core')->escapeHtml(Mage::helper('core/string')->truncate($valueName, 33, '...'));

            $html .= '</a><span class="element"> ' . $element->getElementHtml();

            if ($element->getExplicitApply()) {
                $html .= ' <a href="javascript:void(0)" class="rule-param-apply"><img src="'
                    . $this->getSkinUrl('images/rule_component_apply.gif') . '" class="v-middle" alt="'
                    . $this->__('Apply') . '" title="' . $this->__('Apply') . '" /></a> ';
            }

            $html .= '</span></span>&nbsp;';
        }

        return $html;
    }
}
