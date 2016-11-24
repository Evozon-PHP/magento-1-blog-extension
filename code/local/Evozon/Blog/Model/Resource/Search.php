<?php

/**
 * Blog Search Model
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Store search engine instance
     *
     * @var object
     */
    protected $_engine = null;

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/search_fulltext', 'post_id');
        $this->_engine = $this->getEngine();
    }

    protected function getEngine()
    {
        if (!$this->_engine) {
            $this->_engine = Mage::getModel('evozon_blog/search')->getConfig()->getEngine();
        }

        return $this->_engine;
    }

}
