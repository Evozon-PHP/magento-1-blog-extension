<?php

/**
 * Define statuses for post entity
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_Post_Status extends Mage_Core_Model_Abstract
{
    /**
     * Blog post is published and visible in frontend
     */
    const BLOG_POST_STATUS_PUBLISHED = 1;
    
    /**
     * Blog post needs approval from superadmin before being published
     */
    const BLOG_POST_STATUS_PENDING   = 2;
    
    /**
     * Blog post is still in edit. It is not visible in frontend yet as it has 
     * not been finished yet.
     */
    const BLOG_POST_STATUS_DRAFT     = 3;
    
    /**
     * Blog post has been deleted (archived), but can be restored anytime from
     * archived section
     */
    const BLOG_POST_STATUS_ARCHIVED  = 4;
    
    /**
     * Create an array with option-values from class constants.
     * it is used in grid to show/filter the post row status
     * 
     * @return array
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getAllOptions()
    {    
        $options = array(
            self::BLOG_POST_STATUS_ARCHIVED  => Mage::helper('evozon_blog')->__('Archived (Disabled)'),
            self::BLOG_POST_STATUS_DRAFT     => Mage::helper('evozon_blog')->__('Draft'),
            self::BLOG_POST_STATUS_PENDING   => Mage::helper('evozon_blog')->__('Pending'),
            self::BLOG_POST_STATUS_PUBLISHED => Mage::helper('evozon_blog')->__('Published')
        );
        
        return $options;
    }

    /**
     * Removing the possibility of setting the post status to pending
     * while a mass action it`s used
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getMassActionOptions()
    {
        $options = $this->getAllOptions();
        unset($options[self::BLOG_POST_STATUS_PENDING]);

        return $options;
    }
}
