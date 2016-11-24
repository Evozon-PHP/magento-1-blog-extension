<?php

/**
 * Resource for post-product relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Relations_Product extends Evozon_Blog_Model_Resource_Post_Relations_Abstract
{
    protected function getField()
    {
        return "product_id";
    }

    protected function getResourceName()
    {
        return "evozon_blog/post_relations_product";
    }

    protected function getTableName()
    {
        return "evozon_blog/post_product";
    }

    /**
     * During the search feature,when the product collection it`s loaded,
     * it has to be extended with the products attached to the posts
     * that matched the search term
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $postIds
     * @return array
     */
    public function getRelatedProductsForSearch($postIds)
    {
        if (empty($postIds)) {
            return array();
        }

        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_table, $this->getField())
            ->where('post_id IN (?)', $postIds);

        return $adapter->fetchCol($select);
    }

    /**
     * In order to be able to get the product url by the latest category,
     * the category id is required
     * @see Evozon_Blog_Model_Resource_Post_Relations_Product_Collection -> addRequestPath();
     * @param array $ids
     * @return array
     */
    public function getProductAndCategoriesRelations($ids)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getTable('catalog/category_product'), array('product_id', 'category_id'))
            ->where('product_id IN (?)', $ids)
            ->joinInner(
                array('e' => $this->getTable('catalog/category')),
                'category_id=e.entity_id', array(new Zend_Db_Expr('MAX(level)'))
            )
            ->group(array('product_id'));

        return $adapter->fetchPairs($select);
    }

}