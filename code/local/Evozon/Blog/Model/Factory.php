<?php
/**
 * Evozon Blog Factory class
 * Note: It does not extend Mage_Catalog_Model_Factory/Mage_Core_Model_Factory because EE 1.12 does not have it
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Factory
{
    /**
     * Some of the Magento Enterprise editions are using a system similar to the Community ones for storing URLs
     */
    const MAGENTO_EDITION_VERSION_CHECK = 12;

    /**
     * Path to post_url model alias
     */
    const XML_PATH_POST_URL_MODEL = 'global/evozon_blog/post/url/model';

    /**
     * configuration factory class
     */
    const EVOZON_BLOG_FACTORY_CONFIG_MODEL = 'evozon_blog/factory_config';

    /**
     * indexer resource class
     */
    const EVOZON_BLOG_URL_INDEXER = 'evozon_blog/post_indexer';

    /**
     * indexer collection class
     */
    const EVOZON_BLOG_URL_INDEXER_COLLECTION = 'evozon_blog/post_indexer_collection';

    /**
     * @var Evozon_Blog_Model_Factory_Config_Interface Magento edition configuration factory model
     */
    protected $_factory = null;

    /**
     * Retrieve post_url instance
     *
     * @return Evozon_Blog_Model_Post_Url
     */
    public function getPostUrlInstance()
    {
        return Mage::getModel((string)Mage::getConfig()->getNode(self::XML_PATH_POST_URL_MODEL));
    }

    /**
     * Indexer model depending on the magento edition
     */
    public function getContextInstance()
    {
        return $this->getConfig()->getContextInstance();
    }

    /**
     * Rewrite resource name
     *
     * @return string
     */
    public function getRewriteResource()
    {
        return Mage::getResourceModel(self::EVOZON_BLOG_URL_INDEXER);
    }

    /**
     * Rewrite resource name
     *
     * @return string
     */
    public function getRewriteCollection()
    {
        return Mage::getResourceModel(self::EVOZON_BLOG_URL_INDEXER_COLLECTION);
    }

    /**
     * Resource name to be used in indexer model/resource
     *
     * @return string
     */
    public function getRewriteMainTable()
    {
        return (string) $this->getConfig()->getMainTable();
    }

    /**
     * Accessing required fields required to be updated
     *
     * @return array
     */
    public function getFieldsToUpdate()
    {
        return (array) $this->getConfig()->getFieldsToUpdate();
    }

    /**
     * Getting the factory model and class and configurations
     *
     * @return Evozon_Blog_Model_Factory_Config_Interface
     */
    public function getConfig()
    {
        if (is_null($this->_factory)) {
            $this->_factory = Mage::getSingleton(
                self::EVOZON_BLOG_FACTORY_CONFIG_MODEL,
                array(Evozon_Blog_Model_Factory_Config::EVOZON_BLOG_FACTORY_EDITION => $this->getEdition())
            )->getConfig();
        }

        return $this->_factory;
    }

    /**
     * Getting Magento Edition
     * For Enterprise versions older than 1.12, the Community configurations have to be used
     *
     * @return string
     */
    public function getEdition()
    {
        $edition = Mage::getEdition();
        $version = Mage::getVersionInfo();
        if ($edition == Mage::EDITION_ENTERPRISE && (int) $version['minor'] <= self::MAGENTO_EDITION_VERSION_CHECK)
        {
            return Mage::EDITION_COMMUNITY;
        }

        return $edition;
    }
}