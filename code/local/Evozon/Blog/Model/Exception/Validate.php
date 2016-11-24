<?php

/**
 * Custom exception class for comments, tags, and etc
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Exception_Validate extends Exception
{

    /**
     * The array with the error messages
     * 
     * @var array
     */
    private $_messagesArray;

    /**
     * Constructor
     * Set the error messages to the $_messagesArray
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $_messagesArray
     * @param string $message
     * @param string $code
     * @param string $previous
     */
    public function __construct($_messagesArray, $message = null, $code = null, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->_messagesArray = $_messagesArray;
    }

    /**
     * Return the array with the error messages
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getMessagesArray()
    {
        return $this->_messagesArray;
    }

    
    /**
     * Transforming data
     * The error messages have to be segmented in order to be added as individual errors in the session
     * Used in order to be able to fetch messages as a simple array, without keys and increments
     * 
     * @TODO Remove nested loops
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param nested array $messages
     * @return array
     */
    public function getExceptionMessages()
    {
        $messages = $this->_messagesArray;

        if (!is_array($messages)) {
            return $messages;
        }
        
        $exceptionMessages = array();
        
        foreach ($messages as $id => $type) {
            if (is_array($type)) {
                foreach ($type as $const => $message) {
                    $exceptionMessages[] = $message;
                }
            }

            if (is_string($type)) {
                $exceptionMessages[] = $type;
            }
        }

        return $exceptionMessages;
    }
}
