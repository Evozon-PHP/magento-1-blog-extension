<?php

/**
 * Helper for search functionality
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Helper_Post_Search extends Mage_Core_Helper_Abstract
{ 
    /**
     * Query variable name
     */
    const QUERY_VAR_NAME = 'q';
        
    /*
     * Maximum query length
     */
    const MAX_QUERY_LEN  = 128;
    
    /**
     * null or array with search words added by user.
     *
     * @var null| array
     */
    protected $_searchQuery = null;

    /**
     * Retrieve search query parameter name
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return string
     */
    public function getQueryParamName()
    {
        return self::QUERY_VAR_NAME;
    }
    
   /**
    * return the url where the results will be shown
    * 
    * @return string
    */
    public function getResultUrl()
    {
        $url = Mage::getUrl('blog/post/search');
        
        return $url;
    }
    
    /**
     * split query string into words and return them.
     * 
     * @return array
     */
    public function getSearchQuery()
    {
        // get query param
        if (empty($this->_searchQuery)) {
            $searchQuery = $this->_getRequest()->getParam($this->getQueryParamName());
            
             /* @var $stringHelper Mage_Core_Helper_String */
            $stringHelper = Mage::helper('core/string');
            $searchQueryArray = $stringHelper->splitWords($stringHelper->cleanString(trim($searchQuery)), true, self::MAX_QUERY_LEN);
            $this->_searchQuery = array_map('trim', array_filter($searchQueryArray));            
        }
        
        return $this->_searchQuery;                
    }
    
    /**
     * get max search query length
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return Int
     */
    public function getMaxQueryLen()
    {
        return self::MAX_QUERY_LEN;
    }
    
    /**
     * return search query text.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return String
     */
    public function getSearchQueryText()
    {
        return implode(' ', (array) $this->getSearchQuery());
    }
}
