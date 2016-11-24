<?php

/**
 * Abstract Model
 * 
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
abstract class Evozon_Blog_Model_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * author data
     *
     * @var Evozon_Blog_Model_Author_Interface_IAuthor 
     */
    protected $_author;
    
    /**
     * Return author model
     * 
     * @return Evozon_Blog_Model_Author_Interface_IAuthor
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getAuthor()
    {
        if (!$this->_author) {
            $this->_author = Mage::getModel('evozon_blog/author', $this->getData())
                ->getAuthor();
        }
        
        return $this->_author;
    }
    
    /**
     * Return the config model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Config
     */
    public function getConfigModel()
    {
        return Mage::getSingleton('evozon_blog/config');
    }
}