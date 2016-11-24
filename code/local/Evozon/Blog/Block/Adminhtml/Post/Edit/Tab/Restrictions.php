<?php
/**
 * Restriction rules form
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Prepare the restrictions tab form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $restrictionList = Mage::registry('evozon_current_blog_post_restriction_rules');

        $form = new Varien_Data_Form();

        $fieldsetRenderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('evozon/blog/post/edit/tab/restrictions/fieldset.phtml')
            ->setNewChildUrl(
                $this->getUrl("*/blog_post/newRestrictionHtml/form/evozon_post_restriction_rules_fieldset")
            );

        $fieldset = $form->addFieldset(
            'evozon_post_restriction_rules_fieldset',
            array(
                'legend' => Mage::helper('evozon_blog')->__($restrictionList::FIELDSET_LEGEND)
            ))
            ->setRenderer($fieldsetRenderer);

        $label = Mage::helper('evozon_blog')->__($restrictionList::FIELDSET_LEGEND);
        $fieldset
            ->addField('ruleset', 'text', array('name' => 'ruleset', 'label' => $label, 'title' => $label,))
            ->setRule($restrictionList)
            ->setRenderer(Mage::getBlockSingleton('evozon_blog/adminhtml_post_edit_tab_restrictions_rules'));

        $form->setValues($restrictionList->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->__('Post Access Restrictions');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->__('Post Access Restrictions');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}