<?php

/**
 * Blog post search result
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Search_Posts_Result extends Mage_CatalogSearch_Block_Result
{

    /**
     * Post collection
     *
     * @var Evozon_Blog_Model_Resource_Search_Collection
     */
    protected $_postCollection;

    /**
     * Set enabled/disabled status
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setEnabled(Mage::getModel('evozon_blog/search')->getConfig()->getSearchStatus());
    }
    
    /**
     * Retrieve search list toolbar block
     *
     * @return Mage_Catalog_Block_Product_List
     */
    public function getListBlock()
    {
        return $this->getChild('post_search_list');
    }
    
    /**
     * Set Search Result collection
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    public function setPostListCollection()
    {
        $this->getListBlock()
            ->setCollection($this->_getPostCollection());

        return $this;
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getPostListHtml()
    {
        $this->setPostListCollection();

        return $this->getChildHtml('post_search_list');
    }

    /**
     * Retrieve loaded post collection
     *
     * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
     */
    protected function _getPostCollection()
    {
        if (is_null($this->_postCollection)) {
            $this->_postCollection = Mage::getModel('evozon_blog/search')->getPostCollection();
        }

        return $this->_postCollection;
    }
    
    /**
     * Set search available list orders
     * 
     * @return Evozon_Blog_Block_Search_Result
     */
    public function setListOrders()
    {
        $availableOrders = Mage::getSingleton('evozon_blog/config')->getAttributeUsedForSortByArray();
        $availableOrders = array_merge(
            array('relevance' => $this->__('Relevance')),
            $availableOrders
        );

        $this->getListBlock()
            ->setAvailableOrders($availableOrders)
            ->setDefaultDirection('desc')
            ->setSortBy('relevance');

        return $this;
    }

    /**
     * Retrieve search result count
     *
     * @return string
     */
    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->_getPostCollection()->getSize();

            $this->_getQuery()->setNumResults($size);
            $this->setResultCount($size);
        }

        return $this->getData('result_count');
    }

}
