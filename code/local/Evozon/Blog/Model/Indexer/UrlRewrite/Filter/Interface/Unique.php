<?php

/**
 * Unique path filter interface
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Unique
{
    /**
     * Scan for non-unique paths and resolve them
     *
     * @param array|mixed $data
     * @return mixed
     */
    public function checkAndResolve(&$data);

    /**
     * Add a new path scanner and resolver
     *
     * @param Evozon_Blog_Model_Urlrewrite_Generator_Interface_Unique_Path $checker
     * @return mixed
     */
    public function addScanner($checker);
}