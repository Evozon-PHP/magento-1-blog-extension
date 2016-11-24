<?php

/**
 * Block that overides the toolbar used for the products listing while on search action
 * It has been created in order to get the proper count for items
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{

    /**
     * Keeping all items count
     * @var int
     */
    protected $_totalNum;

    /**
     * Get the total number of items in the collection
     * 
     * @override
     * @return int
     */
    public function getTotalNum()
    {
        ($this->_collection->count());
        if ($this->_totalNum === null) {
            $this->_totalNum = count($this->getCollection()->getItems());
        }

        return $this->_totalNum;
    }

    public function getLastPageNum()
    {
        $collectionSize = (int) $this->getTotalNum();

        if ($collectionSize === 0) {
            return 1;
        }

        $limit = $this->getLimit();
        if ($limit) {
            return ceil($collectionSize / $limit);
        }
        
        return 1;
    }

}
