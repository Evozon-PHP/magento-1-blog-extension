<?php

/**
 * Define statuses for comment entity
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_Comment_Status extends Mage_Core_Model_Abstract
{

    /**
     * Post comment is pending for approval and not visible in frontend
     */
    const BLOG_COMMENT_STATUS_PENDING = 0;

    /**
     * Post comment is approved by superadmin and published
     */
    const BLOG_COMMENT_STATUS_APPROVED = 1;

    /**
     * Post comment is rejected by superadmin. Keep rejected comments separatly
     * before admin decides to delete them for good
     */
    const BLOG_COMMENT_STATUS_MOVED_TO_TRASH = 2;

    /**
     * Post comment is considered Spam.
     */
    const BLOG_COMMENT_STATUS_SPAM = 3;

    /**
     * Message received by user on submitting a comment that has been marked as spam
     */
    const BLOG_COMMENT_STATUS_SPAM_USER_MESSAGE = "The comment was added but marked as spam.";

    /**
     * Message received by user on submitting a comment that needs aproval before being visible
     */
    const BLOG_COMMENT_STATUS_PENDING_USER_MESSAGE = "The comment was added but must de approved by the administrator.";

    /**
     * Message received by user on submitting a comment that needs aproval before being visible
     */
    const BLOG_COMMENT_STATUS_REMOVED_USER_MESSAGE = "The comment has been removed.";

    /**
     * Create an array with option-values from class constants.
     * It is used in grid to show/filter the post row status
     * 
     * @return array
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getOptionArray()
    {
        $options = array(
            self::BLOG_COMMENT_STATUS_PENDING => Mage::helper('evozon_blog')->__('Pending'),
            self::BLOG_COMMENT_STATUS_APPROVED => Mage::helper('evozon_blog')->__('Approved'),
            self::BLOG_COMMENT_STATUS_MOVED_TO_TRASH => Mage::helper('evozon_blog')->__('Moved to trash'),
            self::BLOG_COMMENT_STATUS_SPAM => Mage::helper('evozon_blog')->__('Spam')
        );

        return $options;
    }

    /**
     * Access constants that keeps system responses when user submits a message
     * Used in Evozon_Blog_CommentController
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int | null $key in case it is required a specific reponse, it will be retrieved
     * @return array
     */
    public function getUserMessageOnCommentSubmitArray($key = null)
    {
        $options = array(
            self::BLOG_COMMENT_STATUS_SPAM => Mage::helper('evozon_blog')->__(self::BLOG_COMMENT_STATUS_SPAM_USER_MESSAGE),
            self::BLOG_COMMENT_STATUS_PENDING => Mage::helper('evozon_blog')->__(self::BLOG_COMMENT_STATUS_PENDING_USER_MESSAGE),
            self::BLOG_COMMENT_STATUS_MOVED_TO_TRASH => Mage::helper('evozon_blog')->__(self::BLOG_COMMENT_STATUS_REMOVED_USER_MESSAGE)
        );
        
        if (!is_null($key)) {
            return $options[$key];
        }
        
        return $options;
    }

    /**
     * Create an array with option-values from class constants.
     * It is used in customer account "My Blog Comments" toolbar to show/filter the comments
     *
     * @return array
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getUserCommentFilterArray()
    {
        $options = array(
            -1 =>  Mage::helper('evozon_blog')->__('All'),
            self::BLOG_COMMENT_STATUS_PENDING => Mage::helper('evozon_blog')->__('Pending'),
            self::BLOG_COMMENT_STATUS_APPROVED => Mage::helper('evozon_blog')->__('Approved'),
            self::BLOG_COMMENT_STATUS_MOVED_TO_TRASH => Mage::helper('evozon_blog')->__('Removed'),
        );

        return $options;
    }

}
