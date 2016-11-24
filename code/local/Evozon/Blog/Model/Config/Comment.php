<?php
 
/**
 * Blog Comment Configuration model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Config_Comment
{
    /**
     * Comment configurations -> general settings -> automatic approval
     */
    const GENERAL_AUTOMATIC_APPROVAL = 'auto_approve';
    
    /**
     * Comment configurations -> general settings -> allow guest comments
     */
    const ALLOW_GUEST_COMMENTS = 'allow_guest_comments';

    /**
     * Comment configurations -> general -> reply level
     */
    const SINGLE_PAGE_REPLY_LEVEL = 'allowed_reply_level';
    
    /**
     * Comment configurations -> general settings -> content words number
     */
    const CONTENT_WORDS_NUMBER = 'content_words_number';
    
    /**
     * Comment configurations -> recent comments -> enable comments
     */
    const RECENT_COMMENTS_ENABLED = 'enable_comments_widget';
    
    /**
     * Comment configurations -> recent comments -> number of comments
     */
    const RECENT_COMMENTS_NUMBER = 'comments_number';
    
    /**
     * Comment configurations -> recent comments -> number of words per comment
     */
    const RECENT_COMMENTS_WORDS_NUMBER = 'comment_max_words';
    
    /**
     * Comment configurations -> spam checker -> enable spam checker
     */
    const SPAM_CHECKER_ENABLED = 'enabled';
    
    /**
     * Comment configurations -> spam checker -> http client
     */
    const SPAM_CHECKER_CLIENT = 'http_client';
    
    /**
     * Comment configurations -> spam checker -> http client
     */
    const SPAM_CHECKER_CLIENT_ADAPTER = 'http_client_adapter';
    
    /**
     * Comment configurations -> spam checker -> adapters
     */
    const SPAM_CHECKER_TYPE = 'type';
    
    /**
     * Comment configurations -> akismet spam checker -> api key
     */
    const SPAM_CHECKER_AKISMET_API_KEY = 'akismet_api_key';
    
    /**
     * Comment configurations -> customer account -> enabled
     */
    const CUSTOMER_ACCOUNT_ENABLED = 'enabled';
}