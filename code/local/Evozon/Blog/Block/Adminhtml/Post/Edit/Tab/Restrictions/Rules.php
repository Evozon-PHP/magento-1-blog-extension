<?php
/**
 * Renderer for the restriction rules
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Rules
    implements Varien_Data_Form_Element_Renderer_Interface
{

    /**
     * Render the restriction rules control
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        if ($element->getRule() instanceof Mage_Core_Model_Abstract && $rules = $element->getRule()->getRuleSet()) {
           return $this->_getRenderer($rules)->renderView();
        }

        return '';
    }

    /**
     * Create a block to render the restriction model
     *
     * @param Evozon_Blog_Model_Restriction_Rule_Interface_Rule $ruleModel
     *
     * @return mixed
     */
    protected function _getRenderer($ruleModel)
    {
        return Mage::app()
            ->getLayout()
            ->createBlock($ruleModel->getRendererName())
            ->setForm($ruleModel->getForm())
            ->setModel($ruleModel);
    }
}