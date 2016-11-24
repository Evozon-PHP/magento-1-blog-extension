<?php
/**
 * Indexing model interface to easily switch between rewrite models
 * The implementing classes must be able to:
 * - use urlrewrite filters
 * - prepare rewrite data
 * - context url rewrite fields by which the rewrites will be formated into array
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

interface Evozon_Blog_Model_Indexer_Context_Interface
{

    /**
     * The given class name must be an instance of Evozon_Blog_Model_Indexer_Urlrewrite_Filter_Interface_Unique
     * The unique filter will be the one responsible to validate the created url keys
     * @param string $filter
     */
    public function setUniqueFilter($filter);

    /**
     * Preparing rewrite data for the context
     *
     * @param Varien_Object $post
     * @return mixed
     */
    public function prepareRewriteData(Varien_Object $post);

    /**
     * Returning fields required to format the rewrites to
     *
     * @return array
     */
    public function getRewriteRequiredFields();
}