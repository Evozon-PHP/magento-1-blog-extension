<?php

/**
 * Implementation of strategy, this class will instantiate the required service
 *
 * @package     Evozon_Blog
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author      Lilian Codreanu <lilian.codreanu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Service_Spam_Factory extends Evozon_Blog_Model_Abstract
{
    /**
     * Instance of Evozon_Blog_Model_Service_Spam_Service_Interface
     */
    protected $spamService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->spamService = null;
    }

    /**
     * Return the spam service
     * 
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function getSpamService()
    {
        if (!$this->spamService) {
            $selectedSpamClass = $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_TYPE);
            /** @var Evozon_Blog_Model_Service_Spam_Service_Interface _spamService */
            $this->spamService = Mage::getModel($selectedSpamClass);

            $adapter = Mage::getModel($this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_CLIENT_ADAPTER));

            /** @var Evozon_Blog_Model_Service_Spam_Client_Interface $client */
            $client = Mage::getModel($this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_CLIENT));
            $client->setAdapter($adapter);
            $client->setHeaders(array("Content-Type: application/x-www-form-urlencoded"));

            $this->spamService->setClient($client);
        }

        return $this->spamService;
    }
}
