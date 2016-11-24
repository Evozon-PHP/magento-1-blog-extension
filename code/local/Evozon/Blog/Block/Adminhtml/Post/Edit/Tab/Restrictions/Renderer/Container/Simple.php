<?php
/**
 * Renderer for the restriction rules simple container
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Container_Simple
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
        return array_merge(
            parent::getNewChildSelectOptions(),
            array(
                array(
                'value' => 'evozon_blog/restriction_container_userGroupContainer',
                'label' => Mage::helper('evozon_blog')->__('Add Restrictions For Customer Group')
                )
            )
        );
    }
}