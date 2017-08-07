<?php

/**
 * Implementation of spam checker.
 * Upon adding a new comment, this class will check if the comment is spam and will set the appropriate status
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2017, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Service_Spam_Checker
{
    /** @var $_isCheckerEnabled */
    protected $_isCheckerEnabled;

    /** @var Evozon_Blog_Model_Comment */
    protected $_comment;

    /** @var Evozon_Blog_Model_Config  */
    protected $_configModel;

    /**
     * Evozon_Blog_Model_Service_Comment_Notifications constructor.
     * @param Evozon_Blog_Model_Comment $comment
     */
    public function __construct(Evozon_Blog_Model_Comment $comment)
    {
        $this->_comment = $comment;
        $this->setConfigModel($comment->getConfigModel());

        $this->setIsCheckerEnabled(
            $this->getConfigData(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_ENABLED)
        );

        return $this;
    }

    /**
     * Process if added comment is spam comments
     */
    public function process()
    {
        $comment = $this->getComment();
        if (!$this->getIsCheckerEnabled()) {
            return;
        }

        if (!$comment->isObjectNew())
        {
            return;
        }

        $serviceFactory = Mage::getModel('evozon_blog/service_spam_factory');
        $checkerService = $serviceFactory->getSpamService();

        try {
            if ($checkerService->checkIsSpam($comment) === true) {
                $comment->getResource()->changeStatusToSpamById($comment->getId());
            }
        } catch (\Exception $ex) {
            Mage::logException($ex);
        }

        return $this;
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
    public function setIsCheckerEnabled($value)
    {
        $this->_isCheckerEnabled = $value;
        return $this;
    }

    /**
     * Check if notifications are enabled
     *
     * @return mixed
     */
    public function getIsCheckerEnabled()
    {
        return $this->_isCheckerEnabled;
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
        return $this->getConfigModel()->getCommentsSpamCheckerConfig($key);
    }
}
