<?php

/**
 * Admin author 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Author_AdminAuthor 
    extends Evozon_Blog_Model_Author_Abstract
    implements Evozon_Blog_Model_Author_Interface_IAuthor
{
    /**
     * Return admin first name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getFirstName()
    {
        return $this->getAuthorFirstname();
    }

    /**
     * Return admin last name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getLastName()
    {
        return $this->getAuthorLastname();
    }

    /**
     * Return admin email
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getEmail()
    {
        return $this->getAuthorEmail();
    }
    
    /**
     * Return author backend link to admin account
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBackendLink() 
    {
        return '<a href="' .
            Mage::helper("adminhtml")->getUrl(
                'adminhtml/permissions_user/edit', array('user_id' => $this->getAdminId())) .
            '">' .
            $this->getFirstName() . ' ' . $this->getLastName() .
        '</a>';
    }
}
