<?php
/**
 * Renderer for the restriction rules containers
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Container
    extends Mage_Adminhtml_Block_Template
{

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_template = 'evozon/blog/system/tab/restrictions/container.phtml';

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
     * Create a restriction rendering block
     *
     * @param Evozon_Blog_Model_Restriction_Rule_Interface_Rule $model
     *
     * @return mixed
     */
    protected function _getRenderer($model)
    {
        return Mage::app()
            ->getLayout()
            ->createBlock($model->getRendererName())
            ->setModel($model)
            ->setForm($model->getForm());
    }

    /**
     * Return the type element's name
     *
     * @return string
     */
    protected function getTypeInputName()
    {
        $element = $this->getModel();

        return "{$element->getPrefix()}[{$element->getId()}][type]";
    }

    /**
     * Return the type element's id
     *
     * @return string
     */
    protected function getTypeInputId()
    {
        $element = $this->getModel();

        return "{$element->getPrefix()}__{$element->getId()}__type";
    }

    /**
     * Return the type element's value
     *
     * @return string
     */
    protected function getTypeInputValue()
    {
        return $this->getModel()->getType();
    }

    /**
     * Return the children element's id
     *
     * @return string
     */
    protected function getChildrenListId()
    {
        $element = $this->getModel();

        return "{$element->getPrefix()}__{$element->getId()}__children";
    }

    /**
     * Return the child elements to be renderer
     *
     * @return mixed
     */
    protected function getChildElements()
    {
        return $this->getModel()->getData($this->getModel()->getPrefix());
    }

    /**
     * Create a 'add new child' element
     *
     * @return string
     */
    protected function getAddNewChildElement()
    {
        $element = $this->getModel();
        $button  = $this
            ->getForm()
            ->addField(
                "{$element->getPrefix()}__{$element->getId()}__new_child",
                'select',
                array(
                    'name'      => "{$element->getPrefix()}[{$element->getId()}][new_child]",
                    'values'    => $this->getNewChildSelectOptions(),
                    'value_name'=> $this->getNewChildLabel(),
                )
            )
            ->setRenderer($this->getAddNewChildRenderer());

        return $button->getHtml();
    }

    /**
     * Create a 'remove child' element
     *
     * @return string
     */
    protected function getRemoveChildElement()
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
     * Retrieve the add new child renderer
     *
     * @return object
     */
    protected function getAddNewChildRenderer()
    {
        return Mage::getBlockSingleton('evozon_blog/adminhtml_system_config_form_fields_rule_newchild');
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

    /**
     * Retrieve the new child button's label
     *
     * @return string
     */
    protected function getNewChildLabel()
    {
        $src  = Mage::getDesign()->getSkinUrl('images/rule_component_add.gif');
        $html = '<img src="' . $src . '" class="rule-param-add v-middle" alt="" title="' . Mage::helper('evozon_blog')->__('Add Child Rule') . '"/>';

        return $html;
    }

    /**
     * Return the new child dropdown available choices
     *
     * @return array
     */
    protected function getNewChildSelectOptions()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('evozon_blog')->__('Please choose a restriction to add'))
        );
    }
}