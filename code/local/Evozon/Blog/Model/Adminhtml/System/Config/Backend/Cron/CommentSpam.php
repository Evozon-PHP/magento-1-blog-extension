<?php

/**
 * Spam flusher
 *
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Cron_CommentSpam 
    extends Evozon_Blog_Model_Adminhtml_System_Config_Backend_Cron_Abstract
{
    /**
     * Path to cron_expr
     */
    const CRON_STRING_PATH = 'crontab/jobs/evozon_spam_flush/schedule/cron_expr';
   
    /**
     * Will save the configurations for spam flusher cron
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Diana Botean <diana.botean@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @throws Exception
     */
    protected function _afterSave()
    {
        // try to save the model in config_data
        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($this->getCronExpr())
                ->setPath(self::CRON_STRING_PATH)
                ->save();
        } catch (Exception $e) {
            throw new Exception('Unable to save the cron expression.');
        }
    }
    
    /**
     * Return cron expression
     * 
     * @return string
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Diana Botean <diana.botean@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getCronExpr()
    {  
        /** @var Evozon_Blog_Model_Cron_Config **/
        $config = $this->_getConfig();        
        
        // is cron enabled          
        if ($this->getData($config->getGroupsPathSpamComment(Evozon_Blog_Model_Config_Cron::COMMENT_SPAM_ENABLED))) {           
            // return the hourly expresion
            $hourly = $this->getData($config->getGroupsPathSpamComment(Evozon_Blog_Model_Config_Cron::COMMENT_SPAM_HOURLY));
            if (!$hourly) {
                $time = $this->getData($config->getGroupsPathSpamComment(Evozon_Blog_Model_Config_Cron::COMMENT_SPAM_START_TIME));
                $frequency = $this->getData($config->getGroupsPathSpamComment(Evozon_Blog_Model_Config_Cron::COMMENT_SPAM_FREQUENCY));
                
                $frequencyWeekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
                $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;                
                
                return intval($time[1]) . ' ' . 
                    intval($time[0]) . ' ' . 
                    ($frequency == $frequencyMonthly ? '1' : '*') . 
                    ' * ' . 
                    ($frequency == $frequencyWeekly ? '1' : '*');
            }   
            
            // return the every hour expresion
            $everyHour = $this->getData($config->getGroupsPathSpamComment(Evozon_Blog_Model_Config_Cron::COMMENT_SPAM_HOUR_NUMBER));
            return '0 */' . $everyHour .  ' * * *';            
        } 
        
        return '';
    }
}
