<?php

/**
 * Evozon Blog Indexer config class
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Factory_Config
{
    /**
     * const to set construct params
     */
    const EVOZON_BLOG_FACTORY_EDITION = 'edition';
    
    

    /**
     * configuration class that implements the interface
     * @var Evozon_Blog_Model_Factory_Config_Interface
     */
    protected $_config;

    /**
     * Evozon_Blog_Model_Factory_Config constructor.
     * @param array $data must contain the edition type
     */
    public function __construct(array $data)
    {
        if (!isset($data[self::EVOZON_BLOG_FACTORY_EDITION])){
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10400);
        }
        
        $edition = $data[self::EVOZON_BLOG_FACTORY_EDITION];
        $this->setConfig($edition);
    }

    /**
     * Configuration model that has specific data retained
     *
     * @param string $edition
     * @return $this
     * @throws Exception
     */
    public function setConfig($edition)
    {
        $configModel = sprintf('%s_%s', get_class($this), $edition);
        $this->_config = new $configModel();
        if (!class_exists($configModel) || (!$this->_config instanceof Evozon_Blog_Model_Factory_Config_Interface)){
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10404);
        }

        return $this;
    }

    /**
     * Accessing the configuration model
     * @return Evozon_Blog_Model_Factory_Config_Interface
     */
    public function getConfig()
    {
        return $this->_config;
    }
}
