<?php

/**
 * Abstract class defines all methods that must be implemented by all spam check services
 * 
 * @package     Evozon_Blog
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Service_Spam_Service_Abstract extends Evozon_Blog_Model_Abstract
{    
    /**
     * Set the key of the service as valid
     */
    const SERVICE_VALID_KEY = true;

    /**
     * @var Evozon_Blog_Model_Service_Spam_Client_Interface
     */
    protected $client;

    /**
     * Check if the service is enabled
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @return Boolean
     */
    public function isEnabled()
    {
        return (bool) $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_ENABLED);
    }
    
    /**
     * Get Client
     *
     * @return Evozon_Blog_Model_Service_Spam_Client_Interface|false|Mage_Core_Model_Abstract
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = Mage::getModel('evozon_blog/service_spam_client_zend_http_client');
        }
        return $this->client;
    }

    /**
     * @param Evozon_Blog_Model_Service_Spam_Client_Interface $client
     * @return $this
     */
    public function setClient(Evozon_Blog_Model_Service_Spam_Client_Interface $client)
    {
        $this->client = $client;
        
        return $this;
    }
    /**
     * Verify if the spam checker key is valid
     * If the service doesn't need to validate any key return true
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return boolean
     */
    public function isKeyValid()
    {
        return self::SERVICE_VALID_KEY;
    }
    
    /**
     * Disable spam checker and add an admin notification 
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function disableSpamChecker()
    {
        // disable the service
        Mage::getConfig()->saveConfig(
            Evozon_Blog_Model_Config::XML_PATH_BLOG_COMMENT_SPAM_SERVICE . '/' . Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_ENABLED, 
            0
        );

        $notificationModel = Mage::getModel('adminnotification/inbox');
        $notificationModel->setData(array(
            'severity' => 4,
            'title' => Mage::helper('evozon_blog')->__('Spam checker key'),
            'description' => Mage::helper('evozon_blog')->__('Verify your spam checker key. The actual key is not valid.')
        ));

        $notificationModel->save();
    }
}
