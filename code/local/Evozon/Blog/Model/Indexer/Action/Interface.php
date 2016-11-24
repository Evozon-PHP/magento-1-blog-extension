<?php
/**
 * Indexer Action interface
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

interface Evozon_Blog_Model_Indexer_Action_Interface
{
    /**
     * Main function that will do the reindexing of the urls
     * 
     * @return mixed
     */
    public function reindex();

    /**
     * Deleting rewrites
     * 
     * @return mixed
     */
    public function deleteRewrites();
}