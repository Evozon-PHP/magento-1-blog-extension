<?php

/**
 * Indexing post urls for MagentoCE
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_Context_Enterprise
    extends Evozon_Blog_Model_Indexer_Context_Abstract
    implements Evozon_Blog_Model_Indexer_Context_Interface
{
    /**
     * Adding unique scanners for the enterprise version
     * @return $this
     */
    protected function _addScanners()
    {
        $this->getUniqueFilter()->addScanner(self::EVOZON_BLOG_INDEXER_FILTER_SCANNER_REQUESTPATH);
        return $this;
    }

    /**
     * {@inheritdoc}
     * @param Evozon_Blog_Model_Post $postObject
     * @param array|int              $storeId
     * @param bool|false             $updateUrlStructure
     *
     * @return array
     */
    public function prepareRewriteData(Varien_Object $postObject)
    {
        return array(
            'request_path' => $postObject->getRequestPath(),
            'target_path'  => $postObject->getTargetPath(),
            'is_system'    => 1,
            'guid'         => Mage::helper('core')->uniqHash(),
            'identifier'   => $postObject->getRequestPath(),
            'inc'          => 1,
            'value_id'     => $postObject->getEntityId(),
            'store_id'     => $postObject->getStoreId(),
            'entity_type'  => Evozon_Blog_Model_Post::getEntityType()->getId(),
            'url_rewrite_id' => $postObject->getUrlRewriteId() ? $postObject->getUrlRewriteId() : null
        );
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getRewriteRequiredFields()
    {
        return array(
            'request_path',
            'target_path' ,
            'is_system'   ,
            'guid'        ,
            'identifier'  ,
            'inc'         ,
            'value_id'    ,
            'store_id'    ,
            'entity_type' ,
            'url_rewrite_id'
        );
    }
}