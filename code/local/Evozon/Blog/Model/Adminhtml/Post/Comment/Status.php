<?php

/**
 * Define statuses for post comments allow
 * 
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_Post_Comment_Status extends Mage_Core_Model_Abstract
{
    /*
     * Comments enabled status
     */
    const BLOG_POST_COMMENT_STATUS_ENABLED  = 1;
    /*
     * Comments disabled status
     */
    const BLOG_POST_COMMENT_STATUS_DISABLED = 0;
    
    /**
     * Create an array with option-values from class constants.
     * It is used in grid to show/filter the post row status 
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getOptionArray()
    {    
       $options = array(
            self::BLOG_POST_COMMENT_STATUS_ENABLED  => Mage::helper('evozon_blog')->__('Enabled'),
            self::BLOG_POST_COMMENT_STATUS_DISABLED => Mage::helper('evozon_blog')->__('Disabled')
        );
       
        return $options;
    }
}
