<?php

/**
 * System configurations for blog crons
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Cron_Config extends Evozon_Blog_Model_Config
{    
    /**
     * Comments cron spam configuration 
     */
    const XML_PATH_BLOG_CRON_SPAM_COMMENT = 'evozon_blog_cron/comment_cron_spam';
    
    /**
     * Post cron status configuration 
     */
    const XML_PATH_BLOG_CRON_POST_STATUS = 'evozon_blog_cron/post_cron_status';
    
    /**
     * Comments cron group spam configuration 
     */
    const CRON_GROUPS_SPAM_COMMENT = 'comment_cron_spam';
    
    /**
     * Post cron group status configuration 
     */
    const CRON_GROUPS_POST_STATUS = 'post_cron_status';
    
    /**
     * Get comment cron configuration
     * 
     * @param String $key
     * @return String|Int|Array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getSpamCommentConfig($key = NULL)
    {        
        return $this->_getConfigData(array(
            self::XML_PATH_BLOG_CRON_SPAM_COMMENT), 
            $key
        );       
    }
    
    /**
     * Get post cron configuration
     * 
     * @param String $key
     * @return String|Int|Array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getPostStatusConfig($key = NULL)
    {
        return $this->_getConfigData(array(
            self::XML_PATH_BLOG_CRON_POST_STATUS), 
            $key
        );
    }  
    
    /**
     * Get comment cron groups path configuration
     * 
     * @param String $key
     * @return String|Int
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getGroupsPathSpamComment($key)
    {        
        return 'groups/' . self::CRON_GROUPS_SPAM_COMMENT . '/fields/' . $key. '/value';
    }
    
    /**
     * Get post cron groups path configuration
     * 
     * @param String $key
     * @return String|Int
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getGroupsPathPostStatus($key)
    {
        return 'groups/' . self::CRON_GROUPS_POST_STATUS . '/fields/' . $key. '/value';
    }
}
