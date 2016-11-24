<?php
 
/**
 * Blog Post Configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Config_Post
{
    /**
     * Post configurations -> post listing -> number of posts listed
     */
    const LISTING_NUMBER_OF_POSTS = 'max_posts_perpage';
    
    /**
     * Post configurations -> post listing -> teaser words number
     */
    const LISTING_TEASER_WORDS_COUNT  = 'teaser_words_count';
    
    /**
     * Post configurations -> post listing -> display content
     */
    const LISTING_DISPLAY_CONTENT  = 'listing_display_content';
    
    /**
     * Post configurations -> recent posts -> enabled
     */
    const RECENT_POSTS_ENABLED = 'disable_widget';
    
    /**
     * Post configurations -> recent posts -> template
     */
    const RECENT_POSTS_TEMPLATE = 'template';
    
    /**
     * Post configurations -> recent posts -> posts in widget
     */
    const RECENT_POSTS_NUMBER = 'posts_in_widget';
    
    /**
     * Post configurations -> recent posts -> display date
     */
    const RECENT_POSTS_DISPLAY_DATE = 'display_date';
    
    /**
     * Post configurations -> recent posts -> image style
     */
    const RECENT_POSTS_IMAGE_STYLE = 'image_style';
    
    /**
     * Post configurations -> recent posts -> content length
     */
    const RECENT_POSTS_CONTENT_LENGTH = 'content_length';
    
    /**
     * Post configurations -> recent posts -> display comments
     */
    const RECENT_POSTS_DISPLAY_COMMENTS = 'display_comments';
    
    /**
     * Post configurations -> recent posts -> display tags
     */
    const RECENT_POSTS_DISPLAY_TAGS = 'display_tags';
    
    /**
     * Post configurations -> related posts -> enabled
     */
    const RELATED_POSTS_ENABLED = 'enabled';
    
    /**
     * Post configurations -> related posts -> number of posts visible
     */
    const RELATED_POSTS_NUMBER = 'posts_number';
    
    /**
     * Post configurations -> post url format -> use .html sufix
     */
    const URL_FORMAT_USE_HTML_SUFIX = 'use_html_sufix';
    
    /**
     * Post configurations -> post url format -> month name
     */
    const URL_FORMAT_MONTH_NAME = 'posturl_month_name';
    
    /**
     * Post configurations -> post url format -> day name
     */
    const URL_FORMAT_DAY_NAME = 'posturl_day_name';
    
    /**
     * Post configurations -> post url format -> name
     */
    const URL_FORMAT_NAME = 'posturl_name';
    
    /**
     * Post configurations -> post url format -> numeric
     */
    const URL_FORMAT_NUMERIC = 'posturl_numeric';
    
    /**
     * Post configurations -> post url format -> custom
     */
    const URL_FORMAT_CUSTOM = 'url_custom';
    
    /**
     * Post configurations -> post url format -> keep url structure
     */
    const URL_FORMAT_KEEP_URL_STRUCTURE = 'keep_url_structure';
    
    /**
     * Post configurations -> archive -> enabled
     */
    const ARCHIVE_ENABLED = 'widget_enable';
    
    /**
     * Post configurations -> archive -> header
     */
    const ARCHIVE_HEADER = 'header';
    
    /**
     * Post configurations -> archive -> first segment from url
     */
    const ARCHIVE_URL_SEGMENT = 'url_segment';
    
    /**
     * Post configurations -> archive -> date format
     */
    const ARCHIVE_DATE_FORMAT = 'date_format';
    
    /**
     * Post configurations -> archive -> posts count
     */
    const ARCHIVE_SHOW_COUNT = 'show_count';
    
    /**
     * Post configurations -> archive -> history length
     */
    const ARCHIVE_HISTORY_LENGTH = 'history_length';
    
    /**
     * Post configurations -> tags -> status
     */
    const TAGS_BLOCK_ENABLED = 'enabled';
    
    /**
     * Post configurations -> tags -> header
     */
    const TAGS_BLOCK_HEADER = 'header';
    
    /**
     * Post configurations -> tags -> url segment
     */
    const TAGS_BLOCK_URL_SEGMENT = 'url_segment';
    
    /**
     * Post configurations -> tags -> limit
     */
    const TAGS_BLOCK_LIMIT = 'limit';
    
    /**
     * Post configurations -> tags -> order
     */
    const TAGS_BLOCK_ORDER = 'order';
    
    /**
     * Post configurations -> related posts on product page -> show block
     */
    const RELATED_POSTS_PRODUCT_PAGE_ENABLED = 'enabled';
    
    /**
     * Post configurations -> related posts on product page -> limit
     */
    const RELATED_POSTS_PRODUCT_PAGE_LIMIT = 'limit';
    
    /**
     * Post configurations -> related products on post page -> show block
     */
    const RELATED_PRODUCTS_POST_PAGE_ENABLED = 'enabled';
    
    /**
     * Post configurations -> related products on post page -> limit
     */
    const RELATED_PRODUCTS_POST_PAGE_LIMIT = 'limit';
    
    /**
     * Post configurations -> categories block -> enabled
     */
    const CATEGORIES_BLOCK_ENABLED = 'enabled';
    
    /**
     * Post configurations -> categories block -> limit
     */
    const CATEGORIES_BLOCK_LIMIT = 'limit';
    
    /**
     * Post configurations -> categories block -> category name
     */
    const CATEGORIES_BLOCK_CATEGORY_NAME = 'category_name';
    
    /**
     * Post configurations -> categories block -> category name separator
     */
    const CATEGORIES_BLOCK_CATEGORY_SEPARATOR = 'category_name_separator';
    
    /**
     * Post configurations -> related posts on category page -> show block
     */
    const RELATED_POSTS_CATEGORY_PAGE_ENABLED = 'enabled';
    
    /**
     * Post configurations -> related posts on category page -> limit
     */
    const RELATED_POSTS_CATEGORY_PAGE_LIMIT = 'limit';
    
    /**
     * Post configurations -> rss feed -> enabled
     */
    const RSS_ENABLED = 'enabled';
    
    /**
     * Post configurations -> rss feed -> number of posts
     */
    const RSS_LIMIT = 'limit';
}