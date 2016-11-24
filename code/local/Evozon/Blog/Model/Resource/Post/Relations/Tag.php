<?php

/**
 * Resource for post-tags relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Relations_Tag extends Evozon_Blog_Model_Resource_Post_Relations_Abstract
{

    protected function getField()
    {
        return "tag_id";
    }

    protected function getResourceName()
    {
        return "evozon_blog/post_relations_tag";
    }

    protected function getTableName()
    {
        return "evozon_blog/post_tag";
    }

    /**
     * Retrieve tag ids related to current post and store view
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return int $storeId
     * @return array
     */
    public function getIdsByPostId($postId, $storeId = 0)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->distinct()
            ->from($this->_table, $this->getField())
            ->where($this->getPostField() . '= ?', (int) $postId)
            ->where('store_id = ?', (int) $storeId);

        return $adapter->fetchCol($select);
    }

    /**
     * Retrieve tag and store ids related to current post
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $id
     * @return array
     */
    public function getTagsAndStoresByPostId($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_table, array($this->getField(), 'store_id'))
            ->where($this->getPostField() . '= ?', (int) $id);

        return $adapter->fetchAll($select);
    }
    
    /**
     * Retrieve post store identifiers
     *
     * @param int $tagId
     * @param int $storeId
     * @return array
     */
    public function getPostIdsByTagId($tagId, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->_table, $this->getPostField())
            ->where($this->getField() . '= ?', (int) $tagId)
            ->where('store_id = ?', (int) $storeId);

        return $adapter->fetchCol($select);
    }
}
