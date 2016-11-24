<?php
 
/**
 * Blog General Configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Config_General
{
    /**
     * General configurations -> layout settings -> default layout
     */
    const LAYOUT_DEFAULT = 'default';
    
    /**
     * General configurations -> layout settings -> single page layout
     */
    const LAYOUT_POST_SINGLE_PAGE = 'post_view';
    
    /**
     * General configurations -> search engine optimization settings -> title prefix
     */
    const SEO_TITLE_PREFIX = 'title';
    
    /**
     * General configurations -> search engine optimization settings -> keywords
     */
    const SEO_KEYWORDS = 'keywords';
    
    /**
     * General configurations -> search engine optimization settings -> description
     */
    const SEO_DESCRIPTION = 'description';
    
    /**
     * General configurations -> datetime settings -> default datetime format
     */
    const DATETIME_DEFAULT_FORMAT = 'default_datetime';
    
    /**
     * General configurations -> toolbar and pagination settings -> page parameter name
     */
    const TOOLBAR_AND_PAGINATION_PAGE_NAME = 'page_var_name';
    
    /**
     * General configurations -> toolbar and pagination settings -> filter parameter name
     */
    const TOOLBAR_AND_PAGINATION_FILTER_NAME = 'filter_var_name';
    
    /**
     * General configurations -> post image placeholder settings -> placeholder
     */
    const POST_IMAGE_PLACEHOLDERS_PLACEHOLDER = 'placeholder';
}