<?php
/**
 * File used to hold abstract class
 *
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2017 Evozon (http://www.evozon.com)
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 */

/**
 * Abstract key adaptor model
 *
 * @author   Murgocea Victorita <victorita.murgocea@evozon.com>
 */
abstract class Evozon_Blog_Model_Post_Preview_StoreKeyAdaptor_AbstractKeyAdaptor
{
    /* @var $instance */
    protected  $instance;

    /* @var $postId */
    protected $postId;

    /**
     * Evozon_Blog_Model_Post_Preview_Key_Session constructor
     *
     * @param $postId
     */
    public function __construct($postId = null)
    {
        $this->postId = $postId;
    }

    /**
     * Get post id
     *
     * @return mixed
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Set post id
     *
     * @param $postId
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * Get instance
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getInstance()
    {
        if (!$this->instance) {
            $this->instance = Mage::getSingleton('admin/session');
        }
        return $this->instance;
    }

    /**
     * Set instance
     *
     * @param $instance
     * @return $this
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;
        return $this;
    }

}