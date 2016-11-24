<?php

/**
 * Recent posts RSS feed writer
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Rss_RecentPosts extends Evozon_Blog_Block_Abstract
{
    /**
     * Cache tag constant for feed reviews
     *
     * @var string
     */
    const CACHE_TAG = 'block_html_rss_recentposts';

    /**
     * Post collection
     * 
     * @var Evozon_Blog_Model_Resource_Post_Collection 
     */
    protected $_postCollection = null;

    /**
     * Constructor
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     */
    protected function _construct()
    {        
        parent::_construct();
        
        // set tag cache
        $this->setCacheTags(array(self::CACHE_TAG));
        
        // setting cache to save the rss for 10 minutes
        $this->setCacheKey('rss_recentposts');
        $this->setCacheLifetime(600);
    }

    /**
     * Post collection filtered
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @author  Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return  Evozon_Blog_Model_Resource_Post_Collection
     */
    protected function _getPostCollection()
    {
        if ($this->_postCollection === null) {
            /* @var $postCollection Evozon_Blog_Model_Resource_Post_Collection */
            $postCollection = Mage::getResourceModel('evozon_blog/post_collection');
            $postCollection
                ->setStoreId($this->getRequest()->getParam('store'))
                ->addAttributeToSelect(array('title','url_key','short_content', 'created_at', 'thumbnail', 'post_content'))
                ->addFrontendVisibilityFilters();
            
            // sort collection
            $postCollection->addAttributeToSort('created_at', 'DESC');
            
            if ($this->_getLimit() > 0) {
                $postCollection->setPageSize($this->_getLimit());
            }

            $this->_postCollection = $postCollection;
        }
        
        return $this->_postCollection;
    }

    /**
     * Check if block is enabled in system config
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @return boolean
     */
    protected function _isEnabled()
    {        
        return (bool) $this->getConfigModel()->getPostRssConfig(Evozon_Blog_Model_Config_Post::RSS_ENABLED);
    }

    /**
     * @author  Calin Florea <calin.florea@evozon.com>
     * @return  int
     */
    protected function _getLimit()
    {        
        return (int) $this->getConfigModel()->getPostRssConfig(Evozon_Blog_Model_Config_Post::RSS_LIMIT);
    }

    /**
     * Will generate RSS XML feed
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @author  Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return  string
     */
    protected function _toHtml()
    {
        if (!$this->_isEnabled()) {
            return;
        }
        
        $title = Mage::helper('evozon_blog')->__('Recent posts RSS');

        // RSS header
        $data = array(
            'title' => $title,
            'description' => $title,
            'link' => Mage::getBaseUrl(),
            'charset' => 'UTF-8',
        );

        // create rss model and add the header
        $rssObj = Mage::getModel('rss/rss');
        $rssObj->_addHeader($data);

        // get post collection
        $postCollection = $this->_getPostCollection();
        // get the cms filter
        $filterProcessor = Mage::helper('cms')->getPageTemplateProcessor();

        // if we don't have posts return the rss object
        if (!$postCollection) {
            return $rssObj->createRssXml();
        }

        // for each post create a entry
        foreach ($postCollection as $post) {    
            $imageLink = (string)Mage::helper('evozon_blog/post_image')->init($post, 'thumbnail')->keepFrame(false)->resize(75);
            
            $data = array(
                'title' => $post->getTitle(),
                'link' => $post->getPostUrl(),
                'description' => 
                    '<table>' .
                        '<tr>' .
                            '<td><a href="'.$post->getPostUrl().'"><img src="' . $imageLink .'" border="0" align="left" height="75" width="75"></a></td>' .
                            '<td  style="text-decoration:none;">' . 
                                $filterProcessor->filter(
                                    $post->generateShortContent(
                                        (int) $this->getConfigModel()->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_TEASER_WORDS_COUNT)
                                    )
                                ) . 
                            '</td>' .
                        '</tr>' .
                    '</table>'
            );

            $rssObj->_addEntry($data);
        }

        return $rssObj->createRssXml();
    }
}
