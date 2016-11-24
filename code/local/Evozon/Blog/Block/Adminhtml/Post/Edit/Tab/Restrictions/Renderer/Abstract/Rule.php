<?php
/**
 * Renderer for the restriction rules
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Rule
    extends Mage_Adminhtml_Block_Template
{

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_template = 'evozon/blog/system/tab/restrictions/rule.phtml';

    /**
     * Return a string as the rule's comment
     *
     * @return string
     */
    public function getRuleComment()
    {
        return $this
            ->_getCommentRenderer($this->getModel())
            ->getRuleComment();
    }

    /**
     * Create a comment renderer block from the restriction model
     *
     * @param Evozon_Blog_Model_Restriction_Rule_Interface_Rule $model
     *
     * @return mixed
     */
    protected function _getCommentRenderer($model)
    {
        return Mage::app()
            ->getLayout()
            ->createBlock($model->getCommentRendererName())
            ->setModel($model)
            ->setForm($model->getForm());
    }

    /**
     * Return the type element's name
     *
     * @return string
     */
    public function getTypeInputName()
    {
        $element = $this->getModel();

        return "{$element->getPrefix()}[{$element->getId()}][type]";
    }

    /**
     * Return the type element's id
     *
     * @return string
     */
    public function getTypeInputValue()
    {
        $element = $this->getModel();

        return $element->getType();
    }

    /**
     * Return the type element's value
     *
     * @return string
     */
    public function getTypeInputId()
    {
        $element = $this->getModel();

        return "{$element->getPrefix()}__{$element->getId()}__type";
    }

    /**
     * Create a 'remove child' element
     *
     * @return string
     */
    public function getRemoveChildElement()
    {
        $element = $this->getModel();

        if ($element->getId() === '1') {
            return '';
        }

        $button  = $this
            ->getForm()
            ->addField(
                "{$element->getPrefix()}__{$element->getId()}__remove_child",
                'button',
                array(
                    'element_title'     => $this->__('Remove Restriction Rule'),
                    'element_image_src' => Mage::getDesign()->getSkinUrl('images/rule_component_remove.gif'),
                )
            )
            ->setRenderer($this->getRemoveChildRenderer());

        return $button->getHtml();
    }

    /**
     * Retrieve the remove child element renderer
     *
     * @return object
     */
    protected function getRemoveChildRenderer()
    {
        return Mage::getBlockSingleton('evozon_blog/adminhtml_system_config_form_fields_rule_removeChild');
    }
}