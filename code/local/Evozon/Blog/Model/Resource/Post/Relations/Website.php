<?php

/**
 * Resource for post-website relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Relations_Website extends Evozon_Blog_Model_Resource_Post_Relations_Abstract
{
    protected function getField()
    {
        return "website_id";
    }

    protected function getResourceName()
    {
        return "evozon_blog/post_relations_website";
    }

    protected function getTableName()
    {
        return "evozon_blog/post_website";
    }

}