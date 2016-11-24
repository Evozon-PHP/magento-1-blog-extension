<?php

/**
 * Source model for selecting the post content type displayed in the posts list
 * 
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Post_Components
{
    const POST_COMPONENT_SHORT_CONTENT = 'short_content';
    const POST_COMPONENT_FULL_CONTENT  = 'full_content';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::POST_COMPONENT_SHORT_CONTENT,
                'label' => Mage::helper('evozon_blog')->__('Display post short content')
            ),
            array(
                'value' => self::POST_COMPONENT_FULL_CONTENT,
                'label' => Mage::helper('evozon_blog')->__('Display entire post content')
            ),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            self::POST_COMPONENT_SHORT_CONTENT => Mage::helper('evozon_blog')->__('Display post short content'),
            self::POST_COMPONENT_FULL_CONTENT  => Mage::helper('evozon_blog')->__('Display entire post content'),
        );
    }
}