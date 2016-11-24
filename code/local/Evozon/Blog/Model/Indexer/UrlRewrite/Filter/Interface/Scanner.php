<?php

/**
 * Unique path filter interface
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Scanner
{
    /**
     * Scan the dataset to identify the non-unique paths
     *
     * @param array|mixed $data a data array or an object implementing ArrayAccess
     * @return mixed
     */
    public function validate($data);

    /**
     * Resolve the non-unique paths
     *
     * @param array|mixed $issues
     * @param array|mixed $rewriteData
     * @return mixed
     */
    public function resolve($issues, $rewriteData);
}