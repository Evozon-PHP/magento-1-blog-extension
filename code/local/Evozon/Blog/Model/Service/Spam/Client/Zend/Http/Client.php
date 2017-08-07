<?php

/**
 * Zend Http Client
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
class Evozon_Blog_Model_Service_Spam_Client_Zend_Http_Client extends Zend_Http_Client
    implements Evozon_Blog_Model_Service_Spam_Client_Interface
{

    const USER_AGENT = 'EvozonBlogChecker';

    /**
     * Configuration array, set using the constructor or using ::setConfig()
     *
     * @var array
     */
    protected $config
        = array(
            'maxredirects'    => 5,
            'strictredirects' => false,
            'useragent'       => self::USER_AGENT,
            'timeout'         => 10,
            'adapter'         => 'Evozon_Blog_Model_Service_Spam_Client_Zend_Http_Client_Curl',
            'httpversion'     => self::HTTP_1,
            'keepalive'       => false,
            'storeresponse'   => true,
            'strict'          => true,
            'output_stream'   => false,
            'encodecookies'   => true,
            'rfc3986_strict'  => false,
        );

    /**
     * Constructor
     *
     * Magento's way of passing/receiving arguments in constructor :(
     *
     * @param array|object $args
     */
    public function __construct($args)
    {
        $uri    = null;
        $config = null;

        if (count($args) > 0) {
            $uri    = isset($args[0]) ? $args[0] : $uri;
            $config = isset($args[1]) ? $args[1] : $config;
        }
        parent::__construct($uri, $config);
    }
}