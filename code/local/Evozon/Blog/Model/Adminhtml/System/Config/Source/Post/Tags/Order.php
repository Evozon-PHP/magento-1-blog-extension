<?php

/**
 * Source model for ordering tags 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Post_Tags_Order
{
    /**
     * Options getter
     *
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Evozon_Blog_Block_Post_Tag_Block::EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_RANDOM, 
                'label' => Mage::helper('evozon_blog')->__('Random order')
            ),
            array(
                'value' => Evozon_Blog_Block_Post_Tag_Block::EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_ASC,
                'label' => Mage::helper('evozon_blog')->__('Order by posts number - ASC')
            ),
            array(
                'value' => Evozon_Blog_Block_Post_Tag_Block::EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_DESC, 
                'label' => Mage::helper('evozon_blog')->__('Order by posts number - DESC')
            )
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function toArray()
    {
        $helper = Mage::helper('evozon_blog');
        $labels = $helper->arrayColumn($this->toOptionArray(), 'label');
        $keys = $helper->arrayColumn($this->toOptionArray(), 'value');
        
        return array_combine($keys, $labels);
    }
}
