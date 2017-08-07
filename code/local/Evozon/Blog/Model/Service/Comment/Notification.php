<?php

/**
 * Comment Notification Service
 *
 * @package     Evozon_Blog
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   Copyright (c) 2017, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Service_Comment_Notification
{
    /** @var $_isNotificationEnabled */
    protected $_isNotificationEnabled;

    /** @var Evozon_Blog_Model_Comment */
    protected $_comment;

    /** @var bool $_sendToParentOnly */
    protected $_sendToParentOnly;

    /**
     * @var Evozon_Blog_Model_Config
     */
    protected $_configModel;

    /**
     * Evozon_Blog_Model_Service_Comment_Notifications constructor.
     * @param Evozon_Blog_Model_Comment $comment
     */
    public function __construct(Evozon_Blog_Model_Comment $comment)
    {
        $this->_comment = $comment;
        $this->setConfigModel($comment->getConfigModel());

        $this->setIsNotificationEnabled(
            $this->getConfigData(Evozon_Blog_Model_Config_Comment::XML_PATH_COMMENTS_NOTIFICATIONS)
        );

        $this->setSendNotificationToParentOnly(
            $this->getConfigData(Evozon_Blog_Model_Config_Comment::XML_PATH_SEND_ONLY_TO_PARENT)
        );

        return $this;
    }

    /**
     * Process comments
     */
    public function process()
    {
        if (!$this->getIsNotificationEnabled()) {
            return;
        }

        $approvedStatus = Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED;
        $comment = $this->getComment();

        if ($comment->dataHasChangedFor('status') && $comment->getStatus() == $approvedStatus
        ) {
            $receivers = $this->_getReceivers();
            $collection = $comment->getNotificationReceiversCollection($receivers);
            foreach ($collection as $comment) {
                $this->_sendNotification($comment);
            }
        }
    }

    /**
     * Get email receivers
     *
     * @return array
     */
    protected function _getReceivers()
    {
        $comment = $this->getComment();

        if ($this->getSendNotificationToParentOnly()) {
            return array($comment->getParentId());
        }

        if (!$comment->getPath()) {
            $comment->load($comment->getId());
        }
        $parentIds = explode(',', $comment->getPath());
        array_pop($parentIds);

        return $parentIds;
    }

    /**
     * Send notification
     *
     * @param Evozon_Blog_Model_Comment $parentComment
     * @throws Exception
     */
    protected function _sendNotification(Evozon_Blog_Model_Comment $parentComment)
    {
        $mail = Mage::getModel('core/email_template');
        $mail->setDesignConfig(array(
                'area' => 'frontend',
                'store' => $parentComment->getStoreId()
            )
        )
            ->sendTransactional(
                $this->getEmailTemplate(),
                Mage::getStoreConfig($this->getEmailSenderFromConfig()),
                $parentComment->getAuthor()->getEmail(),
                $parentComment->getAuthor()->getName(),
                array('data' => $this->_buildEmailVars($parentComment)),
                $parentComment->getStoreId()
            );
        if (!$mail->getSentSuccess()) {
            throw new Exception();
        }
    }

    /**
     * Get email template for comments notifications
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->getConfigData(Evozon_Blog_Model_Config_Comment::EMAIL_TEMPLATE);
    }

    /**
     * Get email subject1 for comments notifications
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->getConfigData(Evozon_Blog_Model_Config_Comment::EMAIL_SUBJECT);
    }

    /**
     * Get default email sender for comments notifications
     *
     * @return string
     */
    public function getEmailSenderFromConfig()
    {
        return Evozon_Blog_Model_Config_Comment::XML_PATH_EMAIL_SENDER;
    }

    /**
     * Build email parameters
     *
     * @param Evozon_Blog_Model_Comment $parentComment
     * @return Varien_Object
     */
    protected function _buildEmailVars(Evozon_Blog_Model_Comment $parentComment)
    {
        $data = new Varien_Object();
        $post = Mage::getModel('evozon_blog/post')->load($parentComment->getPostId());
        $post->setStoreId($parentComment->getStoreId());

        $data->setPost($post);
        $data->setSubject($this->getEmailSubject());
        $data->setComment($parentComment);
        $data->setPostUrl($parentComment->getCommentUrl());

        return $data;
    }

    /**
     * Set notify only the parent
     *
     * @param $value
     * @return $this
     */
    public function setSendNotificationToParentOnly($value)
    {
        $this->_sendToParentOnly = $value;
        return $this;
    }

    /**
     * Get notify only the parent
     *
     * @return bool
     */
    public function getSendNotificationToParentOnly()
    {
        return $this->_sendToParentOnly;
    }

    /**
     * Set Comment
     *
     * @param $value
     * @return $this
     */
    public function setComment($value)
    {
        $this->_comment = $value;
        return $this;
    }

    /**
     * Get Comment
     *
     * @return Evozon_Blog_Model_Comment
     */
    public function getComment()
    {
        return $this->_comment;
    }

    /**
     * Check if notifications are enabled
     *
     * @return mixed
     */
    public function setIsNotificationEnabled($value)
    {
        $this->_isNotificationEnabled = $value;
        return $this;
    }

    /**
     * Check if notifications are enabled
     *
     * @return mixed
     */
    public function getIsNotificationEnabled()
    {
        return $this->_isNotificationEnabled;
    }

    /**
     * Set config model for comment
     * 
     * @param $model
     * @return $this
     */
    public function setConfigModel($model)
    {
        $this->_configModel = $model;
        return $this;
    }

    /**
     * Get config model for comment
     *
     * @return mixed
     */
    public function getConfigModel()
    {
        return $this->_configModel;
    }

    /**
     * Accessing parameters from system configurations
     *
     * @param $key
     * @return mixed
     */
    public function getConfigData($key)
    {
        return $this->getConfigModel()->getCommentsConfig($key);
    }
}