<?php

/**
 * Spam Client interface
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
interface Evozon_Blog_Model_Spam_Client_Interface
{
    /**
     * Set the URI for the next request
     *
     * @param  Zend_Uri_Http|string $uri
     *
     * @return Zend_Http_Client
     */
    public function setUri($uri);

    /**
     * Send the request and return response object
     *
     * @param string $method
     *
     * @return Zend_Http_Response
     */
    public function request($method = null);

    /**
     * Load the connection adapter
     *
     * @param Zend_Http_Client_Adapter_Interface|string $adapter
     *
     * @return null
     */
    public function setAdapter($adapter);

    /**
     * Load the connection adapter
     *
     * @return Zend_Http_Client_Adapter_Interface $adapter
     */
    public function getAdapter();
}