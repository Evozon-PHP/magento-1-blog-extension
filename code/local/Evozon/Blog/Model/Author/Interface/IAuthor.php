<?php

/**
 * Author Interface
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
interface Evozon_Blog_Model_Author_Interface_IAuthor
{
    /**
     * Get author first name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getFirstName();
    
    /**
     * Get author last name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getLastName();
    
    /**
     * Get author email
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getEmail();
    
    /**
     * Get author backend link
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBackendLink();
    
}
