<?php

/**
 * Post Url model
 *
 * @package     Evozon_Blog
 * @author      Lilian Codreanu <lilian.codreanu@evozon.com>
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Post_Url
{
    /**
     * URL Model cache tag
     */
    const CACHE_TAG = 'post_url';

    /**
     * @const Day and name configuration: /2015/07/02/sample-post/
     */
    const EVOZON_BLOG_CONFIG_FORMAT_POSTURL_DAY_NAME = '/%year%/%month%/%day%/%title%/';

    /**
     * @const Month and name configuration: 2015/07/sample-post/
     */
    const EVOZON_BLOG_CONFIG_FORMAT_POSTURL_MONTH_NAME = '/%year%/%month%/%title%/';

    /**
     * @const Post name configuration: /sample-post/
     */
    const EVOZON_BLOG_CONFIG_FORMAT_POSTURL_NAME = '/%title%/';

    /**
     * @const Post numeric configuration: /archives/123/
     */
    const EVOZON_BLOG_CONFIG_FORMAT_POSTURL_NUMERIC = '/archives/%id%/';

    /**
     * @const Post custom url XML Path
     */
    const XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_CUSTOM = 'evozon_blog_post/post_url/url_custom';

    /**
     * @const Post custom url keep old url structure
     */
    const XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_KEEP_STRUCTURE = 'evozon_blog_post/post_url/keep_url_structure';
    
    /**
     * post attributes out of which data is required to update rewrites
     * @var array
     */
    protected $_postAttributesForUrl = array(
        'url_key',
        'title',
        'publish_date',
        'url_structure'
    );

    /**
     * @var Varien_Object
     */
    protected $_post;

    /**
     * @var Varien_Object
     */
    protected $_config;

    /**
     * Getting request path based on a post structure
     */
    public function getRequestPath()
    {
        $post = $this->getPost();
        if (is_null($post))
        {
            return false;
        }

        $url = Mage::getSingleton('evozon_blog/factory')
            ->getRewriteResource()
            ->loadRequestPathByTargetPath(
                sprintf(Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::URL_TARGET_PATH_PATTERN, $post->getId()),
                $post->getStoreId()
            );

        return $url ? $url : false;
    }

    /**
     * Get from system config the value selected by admin as post url format.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return String
     */
    public function getSelectedUrlFormat()
    {
        $customUrl = $this->getConfig()->getUrlCustom();
        if (empty($customUrl)) {
            $this->getConfig()->setUrlCustom('/%id%/');
            return $this->getConfig()->getUrlCustom();
        }

        return $customUrl;
    }

    /**
     * Get from system config the selected value for keep old url structure
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function getKeepUrlStructure()
    {
        return (bool) $this->getConfig()->getKeepUrlStructure();
    }

    /**
     * Format Key for URL
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * @return Varien_Object
     */
    public function getConfig()
    {
        if (empty($this->_config)) {
            $this->_config = $this->getUrlConfig();
        }

        return $this->_config;
    }

    /**
     * Return all the configuration of the post url
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getUrlConfig()
    {
        return new Varien_Object($this->_getConfigData());
    }

    /**
     * @return array
     */
    protected function _getConfigData()
    {
        return (array) Mage::getSingleton('evozon_blog/config')->getPostUrlConfig();
    }

    /**
     * Set post object
     *
     * @param $post
     * @return $this
     */
    public function setPost($post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * Retrieve the post object
     *
     * @return mixed
     */
    public function getPost()
    {
        return $this->_post;
    }
    
    /**
     * Accessing the post attributes that are used to create the url key
     * @return array
     */
    public function getPostAttributesForUrl()
    {
        return $this->_postAttributesForUrl;
    }
}