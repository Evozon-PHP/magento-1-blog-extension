<?php

/**
 * Categories list block that is rendered on post single page view 
 * and on posts listing;
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Post_List_Categories extends Evozon_Blog_Block_Post_Abstract
{
    /**
     * Return the post categories
     * 
     * @return array
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getCategories()
    {        
        return $this->getPost()->getCategoryCollection();

    }
}