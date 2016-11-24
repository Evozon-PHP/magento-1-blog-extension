<?php
/**
 * Context class for indexing post urls
 * By the flow, a context contains:
 * - the rewrite rules (unique filters)
 * - a collection of rewrites
 * - 
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

abstract class Evozon_Blog_Model_Indexer_Context_Abstract
{

    /**
     * Filter to check the uniqueness of the created url key
     */
    const EVOZON_BLOG_INDEXER_FILTER_UNIQUE = 'evozon_blog/indexer_urlRewrite_filter_unique';

    /**
     * Scanner attached to the filter to assure unique id path
     */
    const EVOZON_BLOG_INDEXER_FILTER_SCANNER_IDPATH = 'evozon_blog/indexer_urlRewrite_filter_scanner_idPath';

    /**
     * Scanner attached to the filter to assure unique request path
     */
    const EVOZON_BLOG_INDEXER_FILTER_SCANNER_REQUESTPATH = 'evozon_blog/indexer_urlRewrite_filter_scanner_requestPath';

    /**
     * Unique filter engine
     * @var Evozon_Blog_Model_Indexer_Urlrewrite_Filter_Interface_Unique
     */
    protected $_uniqueFilter = null;

    /**
     * Adding scanners to the existing unique filter
     *
     * @return mixed
     */
    abstract protected function _addScanners();

    /**
     * Class construct
     *
     * @param array $args
     */
    public function __construct()
    {
        $this->setUniqueFilter(self::EVOZON_BLOG_INDEXER_FILTER_UNIQUE);
    }

    /**
     * Setting unique filter for checking the urlrewrites for the context
     *
     * @param $filter
     * @return $this
     * @throws Exception
     */
    public function setUniqueFilter($filter)
    {
        $uniqueFilter = Mage::getSingleton($filter);
        if (!$uniqueFilter instanceof Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Unique) {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10010);
        }

        $this->_uniqueFilter = $uniqueFilter;
        $this->_addScanners();

        return $this;
    }

    public function getUniqueFilter()
    {
        return $this->_uniqueFilter;
    }
}