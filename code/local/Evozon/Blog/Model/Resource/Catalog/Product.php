<?php

/**
 * Resource for catalog product
 * It will access the linkage table and update/insert 
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Catalog_Product extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Posts- Category linkage table
     * 
     * @var string
     */
    protected $_postProductTable;

    /**
     * Setting defaults
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/catalog_product', 'rel_id');
        $this->_postProductTable = $this->getTable('evozon_blog/post_product');
    }
    
    /**
     * Delete existent relations between post and catalog categories.
     * Insert into linked table the relations between given category and selected posts
     * 
     * @param int $productId (the id of Mage_Catalog_Model_Category object)
     * @param array | int $posts
     * @return \Evozon_Blog_Model_Resource_Catalog_Category
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function updateProductPosts($productId, array $posts)
    {
        $adapter = $this->_getWriteAdapter();
        
        $oldPostIds = $this->getPostIds($productId);
        
        $insert = array_diff($posts, $oldPostIds);
        $delete = array_diff($oldPostIds, $posts);

        if (!empty($insert)) {
            $data = array();
            foreach ($insert as $postId) {
                 $data[] = array(
                    'product_id' => (int)$productId,
                    'post_id'  => (int)$postId,
                    'position'    => 1
                );
            }
            $adapter->insertMultiple($this->_postProductTable, $data);
        }

        if (!empty($delete)) {
            foreach ($delete as $postId) {
                $condition = array(
                    'product_id = ?' => (int) $productId,
                    'post_id = ?' => (int) $postId,
                );

                $adapter->delete($this->_postProductTable, $condition);
            }
        }
        
        return $this;
    }
    
     /**
     * Retrieve post ids related to current product
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $productId
     * @return array
     */
    public function getPostIds($productId)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_postProductTable, 'post_id')
            ->where('product_id = ?', (int) $productId);

        return $adapter->fetchCol($select);
    }
}
