<?php

/**
 * Exception factory for blog post restrictions
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Exception_RestrictionFactory
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
    protected function __construct()
    {
        $this->_messages = array(
            10000 => 'No restriction rules loaded! Unable to validate!',
            10100 => 'Resolver `%s` already exists and is marked as protected!',
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
    protected function getMessage($code, $params = array())
    {
        $message = '';

        if (isset($this->_messages[$code])) {
            array_unshift($params, $this->_messages[$code]);
            $message = call_user_func_array(array(Mage::helper('evozon_blog'), '__'), $params);
        }

        return  $message;
    }
}