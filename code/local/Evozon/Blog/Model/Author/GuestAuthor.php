<?php

/**
 * Guest author
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Author_GuestAuthor 
    extends Evozon_Blog_Model_Author_Abstract
    implements Evozon_Blog_Model_Author_Interface_IAuthor
{
    /**
     * Return guest first name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getFirstName()
    {        
        return $this->getAuthor();
    }

    /**
     * Return guest last name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getLastName()
    {        
        return '';
    }

    /**
     * Return guest email
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getEmail()
    {
        return $this->getAuthorEmail();
    }
    
    /**
     * Return author name (for guest user display only the name)
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBackendLink()
    {
        return $this->getFirstName();
    }
}
