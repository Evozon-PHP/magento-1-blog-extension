<?php
/**
 * List action class to manage reindexing a more post urls 
 * Or catches to mass actions
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Indexer_Action_List extends Evozon_Blog_Model_Indexer_Action_Abstract
    implements Evozon_Blog_Model_Indexer_Action_Interface
{
    /**
     * Deleting rewrites from the action strategy class
     *
     * @return mixed
     * @throws Evozon_Blog_Model_Exception_IndexFactory
     */
    public function deleteRewrites()
    {
        if (!is_null($this->getPostIds()))
        {
            return $this->_deleteRewrites();
        }

        throw Evozon_Blog_Model_Exception_IndexFactory::instance(10502);
    }
}