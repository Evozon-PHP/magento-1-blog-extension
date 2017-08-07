<?php

/**
 * Source model for spam checker services
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Adminhtml_System_Config_Source_Comment_SpamServices
{
    /**
     * Options getter
     * Creates the config spam checker model to get the spam checkers
     * and sets the array for spam checkers list
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function toOptionArray()
    {        
        $spamChecker = new Evozon_Blog_Model_Service_Spam_Config();
        $adapters = $spamChecker->getDataByServiceAndItemName('crawler', 'services');
        
        $spamCheckersArray = array();
        foreach ($adapters as $spamChecker) {
            if (isset($spamChecker['model']) && isset($spamChecker['label'])) {
                $spamCheckersArray[] = array('value' => $spamChecker['model'], 'label' => $spamChecker['label']);
            }
        }
        
        return $spamCheckersArray;
    }
}