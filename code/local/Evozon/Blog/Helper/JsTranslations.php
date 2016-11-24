<?php

/**
 * Js Translations Helper 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Helper_JsTranslations extends Mage_Core_Helper_Abstract
{
    /**
     * Return the array with all the js translation messages
     * 
     * @return array
     */
    public function getJsTranslations()
    {
        $translationMessages = array(
            'The comment was not submitted. Please try again!' => $this->__('The comment was not submitted. Please try again!'),
            'Something went wrong.' => $this->__('Something went wrong.'),
            'Are you sure you want to delete this comment?' => $this->__('Are you sure you want to delete this comment?'),
            'The status was successfully updated!' => $this->__('The status was successfully updated!'),
            'Insert Gallery...' => $this->__('Insert Gallery...')
        );
        
        return $translationMessages;
    }
}