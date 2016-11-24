<?php
/**
 * 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
abstract class Evozon_Blog_Block_Abstract extends Mage_Core_Block_Template
{
    /**
     * Return the author model
     * 
     * @param array $commentData
     * @return Evozon_Blog_Model_Author_Interface_IAuthor
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getAuthorModel(array $commentData)
    {
        return Mage::getModel('evozon_blog/author', $commentData)->getAuthor();
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