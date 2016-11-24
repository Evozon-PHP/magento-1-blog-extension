<?php

/**
 * Tag url path 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Url_Tag
    extends Mage_Core_Model_Abstract
    implements Evozon_Blog_Model_Url_Interface
{
    /**
     * Return the url path associated with the tag action
     * 
     * @return string
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function setUrlPath()
    {
        return trim(Mage::app()->getRequest()->getPathInfo(), '/');
    }  
}