<?php

/**
 * URL rewrite post matcher for the indexer
 * It is defined in config under the <rewrite_matchers> node
 * It is used for the Enterprise Edition (>1.12) in order to match rewrites to post type
 * It is used for a proper redirect on store switch as well
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_Matcher_Post
{

    /**
     * Instance of request
     *
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * Instance of response
     *
     * @var Mage_Core_Controller_Response_Http
     */
    protected $_response;

    /**
     * Store id (current)
     *
     * @var int $_storeId
     */
    protected $_storeId;

    /**
     * Previous store id (or current if store wasn't switched)
     *
     * @var int
     */
    protected $_prevStoreId;

    /**
     * @var string
     */
    protected $_path;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_request = Mage::app()->getFrontController()->getRequest();
        $this->_response = Mage::app()->getFrontController()->getResponse();
        $this->_storeId = Mage::app()->getStore()->getId();

        $fromStore = $this->_request->getQuery('___from_store');
        $this->_prevStoreId = !empty($fromStore)
            ? Mage::app()->getStore($fromStore)->getId()
            : $this->_storeId;

        return $this;
    }

    /**
     * Match product rewrite
     *
     * @param array $rewriteRow
     * @param string $requestPath
     * @return bool
     */
    public function match(array $rewriteRow, $requestPath)
    {
        $entityTypeId = Evozon_Blog_Model_Post::getEntityType()->getId();

        if ($entityTypeId != $rewriteRow['entity_type']) {
            return false;
        }

        $this->setTargetPath($rewriteRow['target_path']);
        $rewriteParts = explode('/', $rewriteRow['request_path']);
        $rewriteTail = array_pop($rewriteParts);

        $requestParts = explode('/', $requestPath);
        $requestTail = array_pop($requestParts);

        if (strcmp($rewriteTail, $requestTail) === 0) {
            $this->_checkStoreRedirect();
            return true;
        }

        return false;
    }

    /**
     * Redirect to post from another store if the store has been changed
     */
    protected function _checkStoreRedirect()
    {
        if ($this->_prevStoreId == $this->_storeId) {
            return;
        }

        $requestPath = $this->getResource()->loadRequestPathByTargetPath($this->getTargetPath(), $this->_storeId);
        if (!empty($requestPath)) {
            $requestPath = $this->_getBaseUrl() . $requestPath;
            $this->_response->setRedirect($requestPath, 301);
            $this->_request->setDispatched(true);
        }
    }

    protected function getResource()
    {
        return Mage::getResourceSingleton('evozon_blog/post_indexer');
    }

    /**
     * Return current base url
     *
     * @return string
     */
    protected function _getBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK,
            Mage::app()->getStore()->isCurrentlySecure()
        );
    }

    public function setTargetPath($path)
    {
        $this->_path = $path;
        return $this;
    }

    public function getTargetPath()
    {
        return $this->_path;
    }
}