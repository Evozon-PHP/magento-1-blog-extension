<?php

/**
 * Exception factory for blog post indexing
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Exception_IndexFactory
{
    /**
     * Messages placeholder
     * @var array
     */
    protected $_messages = array();

    /**
     * Generate a new Exception object with appropriate code
     * and message
     *
     * @param int   $errorCode
     * @param array $params
     *
     * @return Exception
     */
    public static function instance($errorCode, $params = array())
    {
        $factoryInstance = new self();
        return new Exception($factoryInstance->getMessage($errorCode, $params), $errorCode);
    }

    /**
     * Load exception messages based on code ids
     */
    public function __construct()
    {
        $this->_messages = array(
            10001 => 'Unable to add unique path scanner!',
            10010 => 'Unable to add unique urlrewrite filter!',
            10100 => 'Unable to resolve unique path requirement!',
            10200 => 'Invalid data source type!',
            10400 => 'Missing Magento edition parameter! Please check if the configuration edition has been set on call.',
            10404 => 'No configuration model has been found for this Magento edition. Please extend this module`s feature.',
            10500 => 'An Evozon_Blog_Model_Post entity is required in order to index a blog!',
            10501 => 'An Evozon_Blog_Model_Post entity is required in order to request an indexing action!',
            10502 => 'No post ids have been selected for the delete mass action.',
            10503 => 'Missing object used in order to generate the rewrite path.',
            10601 => 'The given rewrites generator is not valid. Please make use of the requested interface!'
        );
    }

    /**
     * Return a translated message with replaced tokens
     *
     * @param int    $code
     * @param array $params
     *
     * @return mixed|string
     */
    public function getMessage($code, $params = array())
    {
        $message = '';

        if (isset($this->_messages[$code])) {
            array_unshift($params, $this->_messages[$code]);
            $message = call_user_func_array(array(Mage::helper('evozon_blog'), '__'), $params);
        }

        return  $message;
    }
}