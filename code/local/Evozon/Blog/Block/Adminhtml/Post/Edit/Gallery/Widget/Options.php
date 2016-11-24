<?php

/**
 * WYSIWYG options for the Gallery widget
 * It will render each widget parameter from widget.xml as a field
 * The data will be set in the container`s form and saved 
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Widget_Options extends Mage_Widget_Block_Adminhtml_Widget_Options
{

    /**
     * Fieldset getter/instantiation
     *
     * @return Varien_Data_Form_Element_Fieldset
     */
    public function getMainFieldset()
    {
        if ($this->_getData('main_fieldset') instanceof Varien_Data_Form_Element_Fieldset) {
            return $this->_getData('main_fieldset');
        }
        $mainFieldsetHtmlId = 'options_fieldset' . md5($this->getWidgetType());
        $this->setMainFieldsetHtmlId($mainFieldsetHtmlId);
        $fieldset = $this->getForm()->addFieldset($mainFieldsetHtmlId, array(
            'legend' => $this->helper('evozon_blog')->__('Gallery Options'),
            'class' => 'fieldset-wide',
        ));
        
        $fieldset->addField('widget_type', 'hidden', array(
            'label'                 => $this->helper('evozon_blog')->__('Widget Type'),
            'title'                 => $this->helper('evozon_blog')->__('Widget Type'),
            'name'                  => 'widget_type',
            'required'              => true,
            'value'                 => $this->getWidgetType()
        ));

        $this->setData('main_fieldset', $fieldset);

        // add dependence javascript block
        $block = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $this->setChild('form_after', $block);

        return $fieldset;
    }
}
