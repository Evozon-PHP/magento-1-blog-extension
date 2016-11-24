<?php
/**
 * Model for category-post relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Post_Relations_Category extends Evozon_Blog_Model_Post_Relations_Abstract
{
    protected function getModelName()
    {
        return "evozon_blog/post_relations_category";
    }
    
    protected function getSelectedIdsDataName()
    {
        return 'category_ids';
    }
    
    /**
     * Check availability display product in category
     *
     * @param Evozon_Blog_Model_Post $object
     * @param   int $categoryId
     * @return  bool
     */
    public function canBeShownInCategory(Evozon_Blog_Model_Post $object, $categoryId)
    {
        return $this->_getResource()->canBeShownInCategory($object, $categoryId);
    }
    
     /**
     * Get collection of post categories
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCollectionByPostId($postId)
    {
        $collection = Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToSelect(array('name', 'is_active', 'level'))
            ->joinField('post_id', 'evozon_blog/post_category', 'post_id', 'category_id = entity_id', null)
            ->addFieldToFilter('post_id', (int) $postId)
            ->addFieldToFilter('level', array('gt' => 1))
            ->addFieldToFilter('is_active', 1)
            ->addAttributeToFilter('is_blog_category', 1);

        return $collection;
    }

    

}