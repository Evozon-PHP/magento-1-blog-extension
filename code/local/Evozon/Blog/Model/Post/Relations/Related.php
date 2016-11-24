<?php
/**
 * Model for post-related posts relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Post_Relations_Related extends Evozon_Blog_Model_Post_Relations_Abstract
{
    protected function getModelName()
    {
        return "evozon_blog/post_relations_related";
    }
    
    public function getCollectionByPostId($postId)
    {
        return null;
    }

    protected function getSelectedIdsDataName()
    {
        return 'related_posts';
    }

}