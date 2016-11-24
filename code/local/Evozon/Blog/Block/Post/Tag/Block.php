<?php

/**
 * Tags block
 * Depending on the enabled/disabled status, it will arrange the content (tag names)
 * And allow the user to filter the posts by the selected tag
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Tag_Block extends Mage_Core_Block_Template
{

    /**
     * Random tag order.
     * @const int
     */
    const EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_RANDOM = 0;

    /**
     * Order the tags ascending by the nr of posts
     * @const string
     */
    const EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_ASC = 'ASC';

    /**
     * Order the tags descending by the nr of posts
     * @const string
     */
    const EVOZON_BLOG_TAG_BLOCK_CONFIG_SOURCE_DESC = 'DESC';

    /**
     * Number of intervals the range to be divided by
     * @const int
     */
    const EVOZON_BLOG_TAG_BLOCK_RANGE_INTERVALS = 5;

    /**
     * Url segment taken from config
     * @var string
     */
    protected $_urlSegment;

    /**
     * Inferior limit for range
     * @var int
     */
    protected $_minCount;

    /**
     * Superior limit for range
     * @var int
     */
    protected $_maxCount;

    /**
     * Range array by which the tag size will be set
     * @var null | array
     */
    protected $_range = null;

    /**
     * Local caching of tags collection.
     * @var null| Evozon_Blog_Model_Resource_Tag_Collection
     */
    protected $_tagsCollection = null;

    /**
     * The block will be rendered only if it is enabled
     */
    public function __construct()
    {
        if ($this->isEnabled()) {
            return parent::__construct();
        }

       return false;
    }

    /**
     * Template file for the constructor to get
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/tag/block.phtml';
    }

    /**
     * Gets tags collection
     *
     * @return Evozon_Blog_Model_Resource_Tag_Collection
     */
    public function getTagsCollection()
    {
        if (is_null($this->_tagsCollection)) {
            $this->_tagsCollection = $this->_getTagsCollection();
        }

        return $this->_getTagsCollection();
    }

    /**
     * Load the tags collection and set order and limit
     * Sets range data
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Tag_Collection
     */
    protected function _getTagsCollection()
    {
        /* @var $tagsCollection Evozon_Blog_Model_Resource_Tag_Collection */
        $tagsCollection = Mage::getModel('evozon_blog/tag')
            ->getResourceCollection()
            ->addAttributeToSelect(array('name', 'count', 'url_key'))
            ->addAttributeToFilter('count', array('gt'=>0));

        $this->setRange();
        if ($this->getTagOrder()) {
            $tagsCollection->setOrder('count', $this->getTagOrder());
        } else {
            $tagsCollection->getSelect()->orderRand();
        }

        return $tagsCollection->setPageSize($this->getCollectionLimit());
    }
    
    /**
     * While preparing the tags collection, the limits for the range will be set
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Resource_Tag_Collection $collection
     * @return \Evozon_Blog_Block_Post_Tag_Block
     */
    protected function setRange()
    {
        $range = Mage::getResourceModel('evozon_blog/tag')->getRange();

        $this->_minCount = $range['min'];
        $this->_maxCount = $range['max'];
        
        return $this;
    }

    /**
     * Depending on the tags count, the font size will differ
     * The range is created from the minimum and maxim values that have been set before
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $count
     * @return int (from 1 to self::EVOZON_BLOG_TAG_BLOCK_RANGE_INTERVALS)
     */
    public function getSizeFromRange($count)
    {
        if (is_null($this->_range)) {
            $step = ceil(($this->_maxCount - $this->_minCount + 1) / self::EVOZON_BLOG_TAG_BLOCK_RANGE_INTERVALS);
            $this->_range = range($this->_minCount, $this->_maxCount, $step);
        }

        foreach ($this->_range as $size => $interval) {
            if ($count >= $interval) {
                $fontSize = $size;
            }
        }

        $fontSize = $fontSize + 1;
        return $fontSize;
    }

    /**
     * Check if tags block is enabled.
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getSingleton('evozon_blog/config')->getPostTagsConfig('enabled');
    }

    /**
     * Return collection limit
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int
     */
    protected function getCollectionLimit()
    {
        return (int) Mage::getSingleton('evozon_blog/config')->getPostTagsConfig('limit');
    }
    
    /**
     * Accessing tag block configurations to get the url segment used to generate the tag url
     * 
     * @return string
     */
    public function getUrlSegment()
    {
        if (!$this->_urlSegment) {
            $this->_urlSegment = Mage::getSingleton('evozon_blog/config')->getPostTagsConfig('url_segment');
        }

        return $this->_urlSegment;
    }

    /**
     * Accessing tag block configurations to get the order preferences
     * 
     * @return int | string
     */
    protected function getTagOrder()
    {
        return Mage::getSingleton('evozon_blog/config')->getPostTagsConfig('order');
    }

    /**
     * Getting tag url in order to call the filter action
     * 
     * @param string $urlKey
     * @return string
     */
    public function getTagUrl($urlKey)
    {
        return Mage::getBaseUrl() . $this->getUrlSegment() . '/' . $urlKey;
    }

}
