<?php
/**
 * Model for website-post relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Post_Relations_Website extends Evozon_Blog_Model_Post_Relations_Abstract
{
    /**
     * Model name
     * @return string
     */
    protected function getModelName()
    {
        return "evozon_blog/post_relations_website";
    }

    public function getCollectionByPostId($postId)
    {
        return null;
    }

    protected function getSelectedIdsDataName()
    {
        return 'website_ids';
    }

}