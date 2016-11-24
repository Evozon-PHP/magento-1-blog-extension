<?php
/**
 * Comment Observer
 *
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Comment_Observer extends Evozon_Blog_Model_Abstract
{
    /**
     * Delete comments with spam status
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Comment_Observer
     */
    public function flushCommentStatusSpam()
    {
        /* @var $collection Evozon_Blog_Model_Cron */
        $cron = Mage::getModel('evozon_blog/cron');        
        $cron->deleteSpamComments();

        return $this;
    }

    /**
     * When event triggerd (before comment save) call spam check service
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @author  Andreea Macicasan <andreea.macicasan@evozon.com>
     * @event   evozon_blog_comment_save_before
     * @param   Varien_Event_Observer $observer
     * @throws  Exception
     * @return \Evozon_Blog_Model_Adminhtml_Observer
     */
    public function spamCheckService(Varien_Event_Observer $observer)
    {
        if ((bool) $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_ENABLED)) {
            // use spam checker service
            $serviceFactory = Mage::getModel('evozon_blog/spam_factory');
            /** @var Evozon_Blog_Model_Spam_Service_Interface_IChecker $spamService */
            $spamService = $serviceFactory->getSpamService();

            // try/catch any error thrown by spam services
            try {
                if ($spamService->checkIsSpam($observer->getDataObject()) === true) {
                    $observer->getDataObject()->setStatus(Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_SPAM);
                }
            } catch (\Exception $ex) {
                Mage::logException($ex);
            }
        }
        
        return $this;
    }
}
