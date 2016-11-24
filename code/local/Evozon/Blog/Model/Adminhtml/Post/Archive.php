<?php

/**
 * Post Archive Model. Define constants and other related methods to archive.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_Post_Archive extends Mage_Core_Model_Abstract
{
    /**
     * layout type year: display archive data as year - month1, month2, ... month12;
     */
    const BLOG_POST_ARCHIVE_LAYOUT_TYPE_YEAR = 'YEAR';
    
    /**
     * layout type monht: display archive data as month: month1 year, month2 year, month3 year
     */
    const BLOG_POST_ARCHIVE_LAYOUT_TYPE_MONTH = 'MONTH';
}
