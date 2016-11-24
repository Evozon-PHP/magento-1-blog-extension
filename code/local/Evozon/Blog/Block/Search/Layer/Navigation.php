<?php

/**
 * Search Layer Block
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Search_Layer_Navigation extends Mage_Core_Block_Template
{

    /**
     * Whether to display product count for layer navigation items
     * @var bool
     */
    protected $_displayProductCount = null;

    /**
     * Search Collection
     * @var array
     */
    protected $_searchCollection = null;

    /**
     * Sets the propper template if the search has been enabled
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _construct()
    {
        parent::_construct();

        if (Mage::getSingleton('evozon_blog/search')->getConfig()->getSearchStatus()) {
            $this->setTemplate($this->_getTemplate());
        }
    }

    /**
     * Set template file depending on the case
     * 1. if there are products -> set the one with searching options (in Posts or in Products)
     * 2. if there are no products, but only posts -> set the one with the filtering by posts category
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     * 
     * @TODO Implement the 2nd scenario: if there are only posts and no article, filter the posts by category
     */
    protected function _getTemplate()
    {
        return "evozon/blog/search/layer/navigation.phtml";
    }

    /**
     * Retrieve Layer object
     *
     * @return Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        if (!$this->hasData('layer')) {
            $this->setLayer(Mage::getSingleton('evozon_blog/search_layer'));
        }

        return $this->_getData('layer');
    }

    /**
     * Check if the layer navigation block can be displayed
     *
     * @return bool
     */
    public function canShowBlock()
    {
        $layerNavigation = Mage::getSingleton('evozon_blog/search')->getConfig()->getEngine()->isLayerNavigationAllowed();
        if (!$layerNavigation) {
            return false;
        }
        
        if (!$this->getLayer()->getProductCollection()->count() && !$this->getLayer()->getPostCollection()->count())
        {
            return false;
        }

        return true;
    }

    /**
     * Setting the label for the search result filtering
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $type
     * @param int $count
     * @return string
     */
    public function getLabel($type, $count)
    {
        $label = ucwords($type);
        if ($this->shouldDisplayProductCount()) {
            return $this->__('%s (%s)', $label, $count);
        }

        return $this->__('%s', $label);
    }

    /**
     * Url to filter the search results by type
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getSearchUrl($type)
    {
        if (Evozon_Blog_Model_Search_Layer::EVOZON_BLOG_SEARCH_POSTS_TYPE == $type) {
            return Mage::getUrl('evozon_blog/result/blog', array('q' => $this->getQueryText()));
        }

        return Mage::getUrl('catalogsearch/result/index', array('q' => $this->getQueryText()));
    }

    /**
     * Getter for $_displayProductCount
     * @return bool
     */
    protected function shouldDisplayProductCount()
    {
        if ($this->_displayProductCount === null) {
            $this->_displayProductCount = Mage::helper('catalog')->shouldDisplayProductCountOnLayer();
        }
        return $this->_displayProductCount;
    }

    /**
     * Return post collection count
     *
     * @return int
     */
    public function getSearchCollection()
    {
        if (is_null($this->_searchCollection)) {
            $this->_searchCollection = $this->getLayer()->getSearchCollection();
        }
        
        return $this->_searchCollection;
    }

    /**
     * Return query string
     *
     * @return mixed
     */
    public function getQueryText()
    {
        return Mage::helper('catalogsearch')->getQuery()->getQueryText();
    }

}
