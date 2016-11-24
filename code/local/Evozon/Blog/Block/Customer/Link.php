<?php

/**
 * This will add a link to the navigation if it is enabled
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Customer_Link extends Evozon_Blog_Block_Abstract
{

    /**
     * Adds the link to the navigation bar in Mage_Customer_Block_Account_Navigation
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Block_Customer_Link
     */
    public function addLinkToParentBlock()
    {
        $parent = $this->getParentBlock();
        
        if ($parent) {
            if ($this->getConfigModel()->getCommentsCustomerAccountConfig('enabled')) {
                $parent->addLink(
                    'customer_comments',
                    'blog/comment/', 
                    Mage::helper('evozon_blog')->__('My Blog Comments')
                );
            }
        }

        return $this;
    }

}
