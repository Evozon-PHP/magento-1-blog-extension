<?php

/**
 * Cron backend model for post status
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Cron_PostStatus 
    extends Evozon_Blog_Model_Adminhtml_System_Config_Backend_Cron_Abstract
{
    /**
     * Path to cron_expr
     */
    const CRON_STRING_PATH = 'crontab/jobs/evozon_post_status/schedule/cron_expr';
    
    /**
     * Will save the configurations for cron
     * 
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
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getCronExpr()
    {        
        /** @var Evozon_Blog_Model_Cron_Config **/
        $config = $this->_getConfig(); 
        
        // is cron enabled
        if ($this->getData($config->getGroupsPathPostStatus(Evozon_Blog_Model_Config_Cron::POST_STATUS_ENABLED))) {
            // return custom configuration 
            $custom = $this->getData($config->getGroupsPathPostStatus(Evozon_Blog_Model_Config_Cron::POST_STATUS_CUSTOM_SETTINGS));
            if(!$custom) {
                // return default configuration (run cron every hour)
                return '0 * * * *';
            } 
            
            // return the hourly expresion
            $hourly = $this->getData($config->getGroupsPathPostStatus(Evozon_Blog_Model_Config_Cron::POST_STATUS_HOURLY));
            if (!$hourly) {                
                $time = $this->getData($config->getGroupsPathPostStatus(Evozon_Blog_Model_Config_Cron::POST_STATUS_START_TIME));
                return intval($time[1]) . ' ' . intval($time[0]). ' * * *';
            }   
            
            // return the every hour expresion
            $everyHour = $this->getData($config->getGroupsPathPostStatus(Evozon_Blog_Model_Config_Cron::POST_STATUS_HOUR_NUMBER));     
            return '0 */' . $everyHour .  ' * * *';            
        } 
        
        return '';
    }
}
