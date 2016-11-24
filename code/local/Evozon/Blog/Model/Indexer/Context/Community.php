<?php

/**
 * Indexing post urls for MagentoCE
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_Context_Community
    extends Evozon_Blog_Model_Indexer_Context_Abstract
    implements Evozon_Blog_Model_Indexer_Context_Interface
{

    /**
     * A short description of the rewrite
     */
    const URL_REWRITE_DESCRIPTION = 'Evozon Blog Post Url';

    
    /**
     * Adding unique scanners for the community edition urlrewrite type
     * @return $this
     */
    protected function _addScanners()
    {
        $filter = $this->getUniqueFilter();
        $filter->addScanner(self::EVOZON_BLOG_INDEXER_FILTER_SCANNER_REQUESTPATH);

        return $this;
    }

    /**
     * {@inheritdoc}
     * 
     * @param int|array              $storeId
     * @param bool|false             $updateUrlStructure
     * @return array
     */
    public function prepareRewriteData(Varien_Object $postObject)
    {
        return array(
            'store_id'       => $postObject->getStoreId(),
            'id_path'        => $postObject->getIdPath(),
            'request_path'   => $postObject->getRequestPath(),
            'target_path'    => $postObject->getTargetPath(),
            'is_system'      => 1,
            'options'        => null,
            'description'    => self::URL_REWRITE_DESCRIPTION,
            'category_id'    => null,
            'product_id'     => null,
            'url_rewrite_id' => null
        );
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getRewriteRequiredFields()
    {
        return array(
            'store_id',
            'id_path',
            'request_path',
            'target_path',
            'is_system',
            'options',
            'description',
            'category_id',
            'product_id',
            'url_rewrite_id'
        );
    }
}