<?php

/**
 * Customer author
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Author_CustomerAuthor 
    extends Evozon_Blog_Model_Author_Abstract
    implements Evozon_Blog_Model_Author_Interface_IAuthor
{
    /**
     * Return customer first name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getFirstName()
    {
        return $this->getCustomerFirstname();
    }

    /**
     * Return customer last name
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getLastName()
    {
        return $this->getCustomerLastname();
    }

    /**
     * Return customer email
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getEmail()
    {
        return $this->getCustomerEmail();
    }
    
    /**
     * Return author backend link to customer account
     * 
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getBackendLink()
    {
        return '<a href="' .
            Mage::helper("adminhtml")->getUrl(
                'adminhtml/customer/edit', array('id' => $this->getUserId())) .
            '">' .
            $this->getFirstName() . ' ' . $this->getLastName() .
        '</a>';
    }
}
