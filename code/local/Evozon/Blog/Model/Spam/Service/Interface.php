<?php

/**
 * Spam checker interface
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
interface Evozon_Blog_Model_Spam_Service_Interface
{
    /**
     * Return true if the service is enabled
     */
    public function isEnabled();
    
    /**
     * Verify if a comment it's spam
     * 
     * @param Varien_Object $object
     */
    public function checkIsSpam(Varien_Object $object);
    
    /**
     * Disable spam checker and add an admin notification
     */
    public function disableSpamChecker();

    /**
     * Set client
     * 
     * @param Evozon_Blog_Model_Spam_Client_Interface $client
     * @return mixed
     */
    public function setClient(Evozon_Blog_Model_Spam_Client_Interface $client);

    /**
     * Return the client
     * 
     * @return Evozon_Blog_Model_Spam_Client_Interface
     */
    public function getClient();

}
