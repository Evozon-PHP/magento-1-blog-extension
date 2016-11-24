<?php

/**
 * Blog Post listing; it will render all posts according to category/filter/search/tag/period/etc
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_List extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * set pagination limit
     * and set category filter on returned posts collection
     */
    protected function _construct()
    {
        parent::_construct();

        if (null === $this->_postCollection) {
            $this->_postCollection = parent::_getPostCollection();
        }
    }

    /**
     * Before rendering html, but after trying to load cache
     *   extend parent _beforeToHtml in order to optimize performance.
     *
     * Eg: instead of querying database for categories on each single post,
     *   create the list of postIds and make one query.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        if ($this->hasData('posts')) {
            return parent::_beforeToHtml();
        }

        $postCollection = $this->_getPostCollection();

        if ($category = Mage::registry('current_category')) {
            $postCollection->addCategorysFilter($category->getId());
        }

        if ('archive' === $this->getListType()) {
            $postCollection->addFieldToFilter('publish_date', array(
                        'from' => $this->getFromDate(),
                        'to' => $this->getToDate()
                    )
                )
                ->addFieldToFilter('archive_status', array('eq' => 1));
        }

        if ('tag' === $this->getListType()) {
            $postsRelatedToTag = Mage::getResourceSingleton('evozon_blog/post_relations_tag')
                ->getPostIdsByTagId($this->getTag()->getId(), $this->_getStore());

            $postCollection->addFieldToFilter('entity_id', array('in' => $postsRelatedToTag));
        }

        $limit = (int) $this->getConfigModel()->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_NUMBER_OF_POSTS);
        if ($limit) {
            $postCollection->setPage((int) Mage::app()->getRequest()->getParam('page'), $limit);
        }

        $postCollection
            ->addOrder('publish_date', 'DESC')
            ->setProperDateFormat();

        $this->setData('posts', $postCollection);
        return parent::_beforeToHtml();
    }

    /**
     * Set collection
     * 
     * @param Evozon_Blog_Module_Resource_Post_Collection  $collection
     * @return \Evozon_Blog_Block_Post_List
     */
    public function setCollection($collection)
    {
        $this->_postCollection = $collection;
        return $this;
    }
    
    /**
     * Setting page header while on filtering by tag action
     * 
     * @return string
     */
    protected function getHeaderTitle()
    {
        $title = $this->getHeaderDefault();

        if (strpos($title, '%s')) {
            return $this->__($title, $this->getName());
        }

        return $this->__($title);
    }

}
