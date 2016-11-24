<?php

/**
 * Resource for post-category relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Relations_Category extends Evozon_Blog_Model_Resource_Post_Relations_Abstract
{

    protected function getField()
    {
        return "category_id";
    }

    protected function getResourceName()
    {
        return "evozon_blog/post_relations_category";
    }

    protected function getTableName()
    {
        return "evozon_blog/post_category";
    }

    /**
     * Check if current post can be shown in given category id
     *
     * @param Evozon_Blog_Model_Post $post
     * @param int $categoryId
     * @return bool
     */
    public function canBeShownInCategory(Evozon_Blog_Model_Post $post, $categoryId)
    {
        $postId = $post->getId();

        $select = $this->_getReadAdapter()->select()
            ->from($this->_table, 'post_id')
            ->where('post_id= ?', (int) $postId)
            ->where('category_id= ?', (int) $categoryId);

        return $this->_getReadAdapter()->fetchOne($select);
    }

}
