<?php

/**
 * Resource for catalog category
 * It will access the linkage table and update/insert 
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Catalog_Category extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Posts- Category linkage table
     * 
     * @var string
     */
    protected $_postCategoryTable;

    /**
     * Setting defaults
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/catalog_category', 'rel_id');
        $this->_postCategoryTable = $this->getTable('evozon_blog/post_category');
    }

    /**
     * Delete existent relations between post and catalog categories.
     * Insert into linked table the relations between given category and selected posts
     * 
     * @param int $categoryId (the id of Mage_Catalog_Model_Category object)
     * @param array $posts
     * @return \Evozon_Blog_Model_Resource_Catalog_Category
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function updateCategoryPosts($categoryId, array $posts)
    {
        $adapter = $this->_getWriteAdapter();

        $oldPostIds = $this->getPostIds($categoryId);

        $insert = array_diff($posts, $oldPostIds);
        $delete = array_diff($oldPostIds, $posts);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $postId) {
                $data[] = array(
                    'category_id' => (int) $categoryId,
                    'post_id' => (int) $postId,
                    'position' => 1
                );
            }
            $adapter->insertMultiple($this->_postCategoryTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $postId) {
                $condition = array(
                    'category_id = ?' => (int) $categoryId,
                    'post_id = ?' => (int) $postId,
                );

                $adapter->delete($this->_postCategoryTable, $condition);
            }
        }

        return $this;
    }

    /**
     * Retrieve post ids related to current category
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $categoryId
     * @return array
     */
    public function getPostIds($categoryId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_postCategoryTable, 'post_id')
            ->where('category_id = ?', (int) $categoryId);

        return $adapter->fetchCol($select);
    }

    /**
     * Return parent category of current category with own custom design settings
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @see Mage_Catalog_Model_Resource_Category
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Model_Category
     */
    public function getParentDesignCategory(Mage_Catalog_Model_Category $category)
    {
        $pathIds = array_reverse($category->getPathIds());
        $collection = $category->getCollection()
            ->setStoreId(Mage::app()->getStore()->getStoreId())
            ->addAttributeToSelect('page_layout')
            ->addAttributeToSelect('is_blog_category')
            ->addFieldToFilter('entity_id', array('in' => $pathIds))
            ->addAttributeToFilter('custom_use_parent_settings', array(array('eq' => 0), array('null' => 0)), 'left')
            ->addFieldToFilter('level', array('neq' => 0))
            ->setOrder('level', 'DESC')
            ->load();
        return $collection->getFirstItem();
    }

}
