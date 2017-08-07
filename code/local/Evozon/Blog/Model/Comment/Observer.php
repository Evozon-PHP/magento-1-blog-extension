<?php

/**
 * Comment Observer
 *
 * @package     Evozon_Blog
 * @author      Calin Florea <calin.florea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Comment_Observer extends Evozon_Blog_Model_Abstract
{
    /**
     * Delete comments with spam status
     *
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Comment_Observer
     */
    public function flushCommentStatusSpam()
    {
        /* @var $collection Evozon_Blog_Model_Cron */
        $cron = Mage::getModel('evozon_blog/cron');
        $cron->deleteSpamComments();

        return $this;
    }
}
