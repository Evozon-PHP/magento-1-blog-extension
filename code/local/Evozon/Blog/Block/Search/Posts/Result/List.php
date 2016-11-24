<?php

/**
 * Blog Search List Block
 * It is attached to the main catalogsearch_index action
 * To display/render blog articles
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Search_Posts_Result_List extends Evozon_Blog_Block_Abstract
{

    /**
     * @var Evozon_Blog_Model_Resource_Post_Collection 
     */
    protected $_collection;

    /**
     * Declaring the toolbar and setting the default filters and the available filters
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return type
     */
    protected function _beforeToHtml()
    {
        //getting the block
        $toolbar = $this->getToolbarBlock();

        $collection = $this->getCollection();

        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        $toolbar->setCollection($collection);
        $this->setChild('toolbar', $toolbar);

        return parent::_beforeToHtml();
    }

    /**
     * Creating the toolbar block by the name
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Block_Comment_Toolbar
     */
    protected function getToolbarBlock()
    {
        if ($blockName = $this->getToolbarBlockName()) {
            if ($block = $this->getLayout()->getBlock($blockName)) {
                return $block;
            }
        }
    }

    /**
     * Get html code for toolbar
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getToolbarHtml()
    {
        return $this->getChild('toolbar')->toHtml();
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChild('toolbar')->getCurrentMode();
    }

    /**
     * 
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Set post collection
     *
     * @param $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    /*
     * Returns image helper
     * It is used where the images are needed to be displayed
     */

    public function getImageHelper()
    {
        return Mage::helper('evozon_blog/post_image');
    }

}
