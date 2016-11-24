<?php

/**
 * Category related posts block
 * It appears on a category that is not of type blog but still wants for the related posts to be displayed
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Catalog_Category_Posts extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * Basic constructor, set the template if not already set.
     */
    public function __construct()
    {
        if ($this->getIsEnabled()) {
            parent::__construct();
        }
    }

    /**
     * Centered layout 
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/catalog/category/posts/center.phtml';
    }

    /**
     * Get post collection filtered by current category
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function getPostsCollection()
    {
        $category = Mage::registry('current_category');
        $limit = $this->getConfigModel()->getFeaturedPostsCategoryConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_CATEGORY_PAGE_LIMIT);

        if (is_null($this->_postCollection)) {

            /* @var $collection Evozon_Blog_Model_Resource_Post_Collection */
            $postCollection = $this->_getPostCollection();
            $postCollection->addCategorysFilter($category->getId());
            $postCollection->setPage($limit, 0);
            $postCollection->addOrder('publish_date', 'DESC');
        }

        return $this->_postCollection;
    }

    /**
     * Getting is enabled status from config
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getDataSetDefault(
            'is_enabled', 
            $this->getConfigModel()->getFeaturedPostsCategoryConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_CATEGORY_PAGE_ENABLED)
        );
    }

}
