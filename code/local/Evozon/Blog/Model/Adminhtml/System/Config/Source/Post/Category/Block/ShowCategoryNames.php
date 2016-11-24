<?php

/**
 * Source model for category block: show also parent names?
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Post_Category_Block_ShowCategoryNames
{
    /**
     * get the available options
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Evozon_Blog_Block_Post_Category_Block::EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_ONLY_CATNAME, 'label' => Mage::helper('evozon_blog')->__('Only category name')),
            array('value' => Evozon_Blog_Block_Post_Category_Block::EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_TWO_LEVELS, 'label' => Mage::helper('evozon_blog')->__('Parent category name and category name')),
            array('value' => Evozon_Blog_Block_Post_Category_Block::EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_ALL_LEVELS, 'label' => Mage::helper('evozon_blog')->__('All parents names and category name'))
        );
    }
}
