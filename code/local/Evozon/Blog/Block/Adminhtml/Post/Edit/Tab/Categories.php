<?php

/**
 * Load the root categories so that post can be assigned to catalog categories.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Categories extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Categories
{

    /**
     * Native constructor, set template and other defaults
     */
    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('evozon/blog/post/edit/tab/categories.phtml');
        $this->_withProductCount = false;
    }

    /**
     * Get from registry current post model, set in controller
     * 
     * @return \Evozon_Blog_Model_Post
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * Take the category models from post model 
     * & generate array with category ids & cache
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getCategoryIds()
    {
        if (is_null($this->_categoryIds)) {
            $this->_categoryIds = $this->getPost()->getCategoryIds();
        }
        return $this->_categoryIds;
    }

        /**
     * Checks when this block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return false;
    }

    /**
     * Return url to ajax controller
     * 
     * @return String
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/*/categoriesJson', array('_current' => true));
    }
}
