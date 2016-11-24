<?php

/**
 * Url config for MagentoEE that switch to new rewrite model
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Factory_Config_Enterprise
    implements Evozon_Blog_Model_Factory_Config_Interface
{
    /**
     * {@inheritdoc}
     * @param string $methodName
     */
    public function methodNotFound($methodName)
    {
        throw new BadMethodCallException("Method {$methodName} is not defined in " . get_class($this));
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    public function getContextInstance()
    {
        return Mage::getSingleton('evozon_blog/indexer_context_enterprise');
    }


    /**
     * @return string
     */
    public function getMainTable()
    {
        return 'enterprise_urlrewrite/url_rewrite';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getFieldsToUpdate()
    {
        return array('request_path','identifier','guid');
    }
}