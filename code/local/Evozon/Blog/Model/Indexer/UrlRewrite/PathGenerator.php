<?php

/**
 * The path generator re-creates the paths needed for an url_rewrite
 * By calling the function getPaths() - all the required paths will be set on the object
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator
{
    /**
     * URL IDPath pattern for url rewrite
     */
    const URL_IDPATH_PATTERN = 'blog/post/%d';

    /**
     * URL Target path pattern user for rewrite
     */
    const URL_TARGET_PATH_PATTERN = 'blog/post/view/id/%d';

    /**
     * target path identifying a blog post url rewrite instance
     */
    const EVOZON_URL_TARGET_PATH_PATTERN_LIKE = 'blog/post/view/id/';

    /**
     * URL format
     * @var string
     */
    protected $_urlFormat;

    /**
     * @var Varien_Object
     */
    protected $_post;

    public function __construct(Varien_Object $post)
    {
        if (empty($post))
        {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10503);
        }

        $this->setPost($post);
    }

    /**
     * Returning the post object with all the paths created
     * This function sets all possible paths on the object
     *
     * @return Varien_Object
     */
    public function getPaths()
    {
        $post = $this->getPost();
        $post->setRequestPath($this->generateRewriteRequestPath());
        if (!$post->getTargetPath()) {
            $post->setTargetPath($this->createTargetPath());
        }
        $post->setIdPath($this->createIdPath());

        return $post;
    }

    /**
     * Generate the URL string based on specific rules
     * we check if we have an existing path and if it contains a duplicate resolver solution
     * like "-xx", where xx are [0-9]{1,2} we use the old path
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function generateRewriteRequestPath()
    {
        $skipSuffix = false;
        $postObject = $this->getPost();
        $suffix = $this->getUrlSuffix($postObject->getStoreId());
        $createdRequestPath = trim($this->_createRequestPath());

        $requestPath = $postObject->getRequestPath();
        if (!empty($suffix)) {
            $requestPath = substr($requestPath, 0, strpos($requestPath, $suffix));
        }

        $coreRequestPath = substr($requestPath, 0, strrpos($requestPath,'-'));
        if ($coreRequestPath == $createdRequestPath) {
            $createdRequestPath = $postObject->getRequestPath();
            $skipSuffix = true;
        }

        if (!empty($suffix) && !$skipSuffix) {
            $createdRequestPath .= $suffix;
        }

        return $createdRequestPath;
    }

    /**
     * Generate url key based on configuration
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function _createRequestPath()
    {
        $postData = $this->_preparePostData();
        $urlFormat = $this->getUrlFormat();
        $requestPath = str_replace(
            array_map(
                function ($value) {
                    return '%' . $value . '%';
                },
                array_keys($postData)
            ),
            array_values($postData),
            trim($urlFormat, '/')
        );

        $requestPath = preg_replace('/\s+/', '-', $requestPath);
        return strtolower($requestPath);
    }

    /**
     * Prepares post data
     * by preparing data required for different types of url formats
     * (title, month, year, day, etc)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function _preparePostData()
    {
        $post = $this->getPost();
        if ($post->hasTitle()) {
            $post->setTitle($this->formatTextForUrlKey($post->getTitle()));
        }

        list($publishedDate) = explode(' ', $post->getPublishDate());
        if (!empty($publishedDate)) {
            list($postYear, $postMonth, $postDay) = explode('-', $publishedDate);
            $post->setYear($postYear);
            $post->setMonth($postMonth);
            $post->setDay($postDay);
        }

        $postData = array_filter($post->getData(), 'is_string');
        $postData['id'] = $post->getEntityId();

        return $postData;
    }

    /**
     * Format Key for URL
     *
     * @param string $str
     * @return string
     */
    public function formatTextForUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Get product url suffix and add to post urlKey

     * @param null|int $storeId
     * @return string
     */
    public function getUrlSuffix($storeId = null)
    {
        $suffix = Mage::helper('catalog/product')->getProductUrlSuffix($storeId);
        if(!empty($suffix) && strpos($suffix,'.') === false ) {
            $suffix = '.'.$suffix;
        }

        return $suffix;
    }

    /**
     * Getting the url format
     * @return string
     */
    public function getUrlFormat()
    {
        if (is_null($this->_urlFormat)) {
            $this->_urlFormat = $this->_createUrlFormat();
        }

        return $this->_urlFormat;
    }

    /**
     * Creating the url format based on the object data
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function _createUrlFormat()
    {
        $post = $this->getPost();
        $model = $this->getUrlModel();
        $urlFormat = $model->getSelectedUrlFormat();

        if ($model->getKeepUrlStructure()) {
            $urlFormat = $post->getUrlStructure();
        }

        if ($post->getUrlKey()) {
            $urlFormat = preg_replace('/(((%id%).)$|((%title%).+)$)/', '%url_key%', $urlFormat, 1);
        }

        return $urlFormat;
    }


    /**
     * Create idPath for a given post id
     *
     * @return string
     */
    public function createIdPath()
    {
        return sprintf(self::URL_IDPATH_PATTERN, $this->getPost()->getEntityId());
    }

    /**
     * Create targetPath for a given post id
     *
     * @return string
     */
    public function createTargetPath()
    {
        return sprintf(self::URL_TARGET_PATH_PATTERN, $this->getPost()->getEntityId());
    }


    public function getUrlModel()
    {
        return Mage::getSingleton('evozon_blog/factory')->getPostUrlInstance();
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
}