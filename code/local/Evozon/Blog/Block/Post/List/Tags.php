<?php

/**
 * Tags list block that is rendered on post single page view 
 * and on posts listing;
 * 
 * It is also created in the Recent Posts widget/block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Block_Post_List_Tags extends Evozon_Blog_Block_Post_Abstract
{
    
    /**
     * Url segment taken from config
     * @var string
     */
    protected $_urlSegment;
    

    /**
     * Get template
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/list/tags.phtml';
    }
    
    /**
     * Accessing tag block configurations to get the url segment used to generate the tag url
     * 
     * @return string
     */
    public function getUrlSegment()
    {
        if (!$this->_urlSegment) {
            $this->_urlSegment = Mage::getSingleton('evozon_blog/config')->getPostTagsConfig(Evozon_Blog_Model_Config_Post::TAGS_BLOCK_URL_SEGMENT);
        }

        return $this->_urlSegment;
    }

    /**
     * Return the post categories
     * 
     * @return array
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getTags()
    {
        return $this->getPost()->getTagsCollection();
    }

    /**
     * Setting tag url in order to call the filter action
     * 
     * @param string $urlKey
     * @return string
     */
    public function getTagUrl($urlKey)
    {
        return Mage::getBaseUrl() . $this->getUrlSegment() . '/' . $urlKey;
    }
}
