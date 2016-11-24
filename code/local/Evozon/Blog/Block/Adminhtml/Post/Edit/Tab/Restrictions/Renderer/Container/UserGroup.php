<?php
/**
 * Renderer for the restriction rules user group container
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Container_UserGroup
    extends Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Container
    implements Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Interface_Container
{

    /**
     * Return the new child dropdown choices
     *
     * @return array
     */
    protected function getNewChildSelectOptions()
    {
        return array(
            array('value' => '', 'label' => Mage::helper('evozon_blog')->__('Please choose a restriction to add')),

            array(
                'value' => 'evozon_blog/restriction_rule_postActionToComponent',
                'label' => Mage::helper('evozon_blog')->__('Restrict post action to specific section')
            ),

            array(
                'value' => 'evozon_blog/restriction_rule_postFullFromAction',
                'label' => Mage::helper('evozon_blog')->__('Restrict entire post from action')
            ),

        );
    }
}