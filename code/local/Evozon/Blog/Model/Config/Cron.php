<?php

/**
 * Blog Cron Configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Config_Cron
{
    /**
     * Cron configurations -> spam comments cronjob -> enabled
     */
    const COMMENT_SPAM_ENABLED = 'enabled';
    
    /**
     * Cron configurations -> spam comments cronjob -> hourly
     */
    const COMMENT_SPAM_HOURLY = 'hourly';
    
    /**
     * Cron configurations -> spam comments cronjob -> hour_number
     */
    const COMMENT_SPAM_HOUR_NUMBER = 'hour_number';
    
    /**
     * Cron configurations -> spam comments cronjob -> start time
     */
    const COMMENT_SPAM_START_TIME = 'time';
    
    /**
     * Cron configurations -> spam comments cronjob -> frequency
     */
    const COMMENT_SPAM_FREQUENCY = 'frequency';
    
    /**
     * Cron configurations -> change post status cronjob -> enabled
     */
    const POST_STATUS_ENABLED = 'enabled';
    
    /**
     * Cron configurations -> change post status cronjob -> custom setting
     */
    const POST_STATUS_CUSTOM_SETTINGS = 'custom';
    
    /**
     * Cron configurations -> change post status cronjob -> hourly
     */
    const POST_STATUS_HOURLY = 'hourly';
    
    /**
     * Cron configurations -> change post status cronjob -> hour_number
     */
    const POST_STATUS_HOUR_NUMBER = 'hour_number';
    
    /**
     * Cron configurations -> change post status cronjob -> start time
     */
    const POST_STATUS_START_TIME = 'time';
}
