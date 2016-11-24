<?php
/**
 * Full action class to manage reindexing all post urls
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Indexer_Action_Full extends Evozon_Blog_Model_Indexer_Action_Abstract
    implements Evozon_Blog_Model_Indexer_Action_Interface
{
    /**
     * Deleting all existing rewrites
     * @return $this;
     */
    protected function _deleteRewrites() {
        $this->getResource()->deleteFullRewrites();

        return $this;
    }

}