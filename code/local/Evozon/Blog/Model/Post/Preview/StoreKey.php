<?php
/**
 * File used to hold class for preview store key
 *
 * @package     Evozon_Blog
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 */

/**
 * Class for Store key
 *
 * @author   Murgocea Victorita <victorita.murgocea@evozon.com>
 */
class Evozon_Blog_Model_Post_Preview_StoreKey
    implements Evozon_Blog_Model_Post_Preview_KeyInterface
{
    /**
     * @var Evozon_Blog_Model_Post_Preview_KeyInterface
     */
    protected $adapter;

    /**
     * Main construct
     * Evozon_Blog_Model_Post_Preview_StoreKey constructor.
     * @param Evozon_Blog_Model_Post_Preview_KeyInterface $adapter
     */
    public function __construct(
        Evozon_Blog_Model_Post_Preview_KeyInterface $adapter
    )
    {
        $this->adapter = $adapter;
    }

    /**
     * Get adapter
     * @return Evozon_Blog_Model_Post_Preview_KeyInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Check if instance already has key
     *
     * @return bool
     */
    public function hasKey()
    {
        return $this->getAdapter()->hasKey();
    }

    /**
     * Set new key on instance
     *
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->getAdapter()->setKey($key);

        return $this;
    }

    /**
     * Get Key
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAdapter()->getKey();
    }

    /**
     * Get session saved keys
     *
     * @return array|bool
     */
    public function getKeys()
    {
        return $this->getAdapter()->getKeys();
    }

}