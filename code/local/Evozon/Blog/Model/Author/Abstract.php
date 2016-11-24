<?php

/**
 * Admin author 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
abstract class Evozon_Blog_Model_Author_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Return the first name concatenate with last name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }  
}
