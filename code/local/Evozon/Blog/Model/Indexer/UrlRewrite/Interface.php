<?php
/**
 * Interface to define the functions required to have in order to implement rewrites
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Indexer_UrlRewrite_Interface
{
    /**
     * Prepare rewrite data to be added to the indexer
     * @return mixed
     */
    public function prepareRewrites();

    /**
     * Creates rewrites consisting of the fields required by the magento edition it is being used on
     * @return mixed
     */
    public function createRewrites();

    /**
     * Validates the created rewrites before saving them in the database
     * @return mixed
     */
    public function validateRewrites();
}