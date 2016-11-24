<?php

/**
 * Category block: categories set as blog and have posts attached.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Category_Block extends Evozon_Blog_Block_Abstract
{

    /**
     * Show only category name.
     * 
     * @const
     */
    const EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_ONLY_CATNAME = 1;

    /**
     * Show current category name and parent category name
     * 
     * @const
     */
    const EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_TWO_LEVELS = 2;

    /**
     * Show all parents and subparents category names.
     * 
     * @const
     */
    const EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_ALL_LEVELS = 3;

    /**
     * Local caching of category collection.
     *
     * @var null| Mage_Catalog_Model_Resource_Category_Collection
     */
    protected $_categoryCollection = null;

    /**
     * Local caching for category names
     * 
     * @var Array 
     */
    protected $_catNames = array();

    /**
     * The block will be rendered only if it is enabled
     */
    public function __construct()
    {
        if ($this->isEnabled()) {
            return false;
        }

        parent::__construct();
    }

    /**
     * Template file for the constructor to get
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/category/block.phtml';
    }

    /**
     * Load the category collection and add joins and filters.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Catalog_Category_Collection
     */
    protected function _getCategoryCollection()
    {
        if (is_null($this->_categoryCollection)) {

            $catCollection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect(array('name', 'is_active', 'level', 'is_blog_category'))
                ->joinField('category_id', 'evozon_blog/post_category', 'category_id', 'category_id = entity_id', null)
                ->addFieldToFilter('level', array('gt' => 1))
                ->addFieldToFilter('is_active', 1)
                ->addAttributeToFilter('is_blog_category', 1);

            if ($limit = $this->getCollectionLimit()) {
                $catCollection->setPageSize($limit);
            }

            $catCollection->groupByAttribute('entity_id');

            $this->_categoryCollection = $catCollection;
        }

        return $this->_categoryCollection;
    }

    /**
     * Check if category block is enabled.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return Bool
     */
    public function isEnabled()
    {
        return (bool) $this->getConfigModel()->getPostCatalogCategoriesConfig(Evozon_Blog_Model_Config_Post::CATEGORIES_BLOCK_ENABLED);
    }

    /**
     * Return collection limit
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return Int
     */
    protected function getCollectionLimit()
    {
        return (int) $this->getConfigModel()->getPostCatalogCategoriesConfig(Evozon_Blog_Model_Config_Post::CATEGORIES_BLOCK_LIMIT);
    }

    /**
     * Generate category name according to settings from config.
     * Creates a local caching, in case same category name is requested twice
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param Mage_Catalog_Model_Category $category
     * @return String
     *
     * @TODO Refractory
     *
     */
    public function getCategoryName(Mage_Catalog_Model_Category $category)
    {
        if (!array_key_exists($category->getId(), $this->_catNames)) {

            $catNameSeparator = $this->getConfigModel()->getPostCatalogCategoriesConfig(Evozon_Blog_Model_Config_Post::CATEGORIES_BLOCK_CATEGORY_SEPARATOR);
            $catNameConfig = $this->getConfigModel()->getPostCatalogCategoriesConfig(Evozon_Blog_Model_Config_Post::CATEGORIES_BLOCK_CATEGORY_NAME);

            // if two category level names will be shown: parent and current
            if (self::EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_TWO_LEVELS == $catNameConfig) {
                $parent = $category->getParentCategory();
                if ($parent->getLevel() > 1) {
                    $catName[] = $parent->getName();
                }

                // if all parent categories names will be shown;
            } elseif (self::EVOZON_BLOG_CATEGORY_BLOCK_CONFIG_SOURCE_ALL_LEVELS == $catNameConfig) {
                $catParents = $category->getParentCategories();
                foreach ($catParents as $catId => $parent) {
                    if ($parent->getLevel() > 1 && $catId != $category->getId()) {
                        $catName[] = $parent->getName();
                    }
                }
            }

            $catName[] = $category->getName();
            $this->_catNames[$category->getId()] = implode(' ' . $catNameSeparator . ' ', $catName);
        }

        return $this->_catNames[$category->getId()];
    }

}
