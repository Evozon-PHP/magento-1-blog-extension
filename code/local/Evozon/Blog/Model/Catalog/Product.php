<?php
/**
 * Post link with catalog product model
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Catalog_Product extends Mage_Catalog_Model_Product
{
    /**
     * constructor
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/catalog_product');
    }
}
