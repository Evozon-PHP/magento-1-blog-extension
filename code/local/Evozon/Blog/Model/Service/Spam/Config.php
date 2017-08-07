<?php

/**
 * Spam checker configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Service_Spam_Config extends Varien_Simplexml_Config
{
    /**
     * Spam checker configuration filename
     */
    const CONFIG_FILE = 'spam.xml';
    
    /**
     * Constructor
     *
     * @param null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $this->setXml(
            Mage::getConfig()
                ->loadModulesConfiguration(self::CONFIG_FILE)
                ->getNode()
        );
    }

    /**
     * Return crawlers name - adapters and clients
     *
     * @param string $serviceName
     * @return array|string
     */
    public function getDataByServiceName($serviceName)
    {
        return $this->getNode($serviceName)->asArray();
    }
    
    /**
     * Get data from child nodes of the services
     *
     * @param string $serviceName
     * @param string $nodeName
     * @return array|string
     * @throws Mage_Core_Exception
     */
    public function getDataByServiceAndItemName($serviceName, $nodeName)
    {
        $node = $this->getNode($serviceName . '/' . $nodeName);
        if (!$node) {
            Mage::throwException(sprintf('Missing node name %s in config file.', $nodeName));
        }

        return $node->asArray();
    }
}
