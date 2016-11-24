<?php
/**
 * Restriction rules form 'remove child' control
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_System_Config_Form_Fields_Rule_RemoveChild
    extends Mage_Core_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * HTML code for the remove child button
     */
    const HTML_CODE_REMOVE_BUTTON = <<<HTML
    <span class="rule-param">
        <a href="javascript:void(0)" class="rule-param-remove" title="%s">
            <img src="%s" alt="" class="v-middle" />
        </a>
    </span>
HTML;

    /**
     * Render the html control
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = sprintf(
            self::HTML_CODE_REMOVE_BUTTON,
            $element->getElementTitle(),
            $element->getElementImageSrc()
        );

        return $html;
    }
}
