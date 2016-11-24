<?php
/**
 * Renderer for the restriction rules comment
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Comment
    extends Mage_Adminhtml_Block_Template
{

    /**
     * The format of the name attribute of the comment element
     *
     * @var string
     */
    protected $_commentElementIdFormat   = "%s__%s__%s";

    /**
     * The format of the id attribute of the comment element
     *
     * @var string
     */
    protected $_commentElementNameFormat = "%s[%s][%s]";

    /**
     * Return an array containing the comment options in the form of an array
     * of properties to build the controls
     *
     * @return array
     */
    protected abstract function _getCommentOptions();

    /**
     * Return the rule comment
     *
     * @return string
     */
    public function getRuleComment()
    {
        //translate the comment before processing it
        $comment        = $this->__($this->getComment());
        $commentOptions = (array)$this->_getCommentOptions();

        if (0 === count($commentOptions)) {
            return $this->renderComment(array($comment));
        }
        //build the comment options
        $commentOptions = $this->_buildCommentOptions($commentOptions);

        return $this->renderComment(
            array_merge(array('comment' => $comment), $commentOptions)
        );
    }

    /**
     * Render the rule comment
     *
     * @param array $options
     *
     * @return string
     */
    public function renderComment($options = array())
    {
        return call_user_func_array('sprintf', $options);
    }

    /**
     * Build the controls that will be used in the rule comment
     *
     * @param array $optionsData
     *
     * @return array
     */
    protected function _buildCommentOptions($optionsData = array())
    {
        $optionsHtml   = array();
        $optionElement = '';

        if (empty($optionsData)) {
            return $optionsData;
        }

        foreach ($optionsData as $key => $option) {
            //add control id and name before adding it to the form
            $option['element_id'] = $this->_getCommentElementId($key);
            $option['name']       = $this->_getCommentElementName($key);

            //if the option has a method defined use that one otherwise
            //build the method name
            $method = isset($option['method']) ? $option['method'] : 'get' . ucfirst($option['type']) . 'Element';
            if (method_exists($this, $method)) {
                $optionElement = $this->$method($option);
            }

            if ($optionElement instanceof Varien_Data_Form_Element_Abstract) {
                $optionsHtml[] = $optionElement->getHtml();
            }

            if (is_string($optionElement)) {
                $optionsHtml[] = $optionElement;
            }
        }

        return $optionsHtml;
    }

    /**
     * Create a dropdown input filed and add it to the form
     *
     * @param array $optionsData
     *
     * @return mixed
     */
    protected function getSelectElement(array $optionsData = array())
    {
        $element = $this->getForm()->addField($optionsData['element_id'], 'select', $optionsData);

        $element->setRenderer(
            Mage::getBlockSingleton('evozon_blog/adminhtml_system_config_form_fields_rule_editable')
        );

        return $element;
    }

    /**
     * Return the comment element id string
     *
     * @param string $controlName
     *
     * @return string
     */
    protected function _getCommentElementId($controlName = '')
    {
        return sprintf(
            $this->_commentElementIdFormat,
            $this->getModel()->getPrefix(),
            $this->getModel()->getId(),
            $controlName
        );
    }

    /**
     * Return the comment element name string
     *
     * @param string $controlName
     *
     * @return string
     */
    protected function _getCommentElementName($controlName = '')
    {
        return sprintf(
            $this->_commentElementNameFormat,
            $this->getModel()->getPrefix(),
            $this->getModel()->getId(),
            $controlName
        );
    }
}