<?php
/**
 * File used to hold class for Session
 *
 * @package     Evozon_Blog
 * @author      Murgocea Victorita <victorita.murgocea@evozon.com>
 * @copyright   Copyright (c) 2017, Evozon
 */

/**
 * Session model
 *
 * @author   Murgocea Victorita <victorita.murgocea@evozon.com>
 */
class Evozon_Blog_Model_Post_Preview_StoreKeyAdaptor_Session
    extends Evozon_Blog_Model_Post_Preview_StoreKeyAdaptor_AbstractKeyAdaptor
    implements Evozon_Blog_Model_Post_Preview_KeyInterface
{

    /**
     * Check if instance allready has key
     *
     * @return bool
     */
    public function hasKey()
    {
        $keys = $this->getKeys();
        return isset($keys[$this->getPostId()]);
    }

    /**
     * Set new key on instance
     *
     * @param $key
     * @return $this
     */
    public function setKey($key)
    {
        $keys = $this->getKeys();
        $keys[$this->getPostId()] = $key;
        $this->getInstance()->setData('preview_keys', $keys);

        return $this;
    }

    /**
     * Get Key
     *
     * @return mixed
     */
    public function getKey()
    {
        $key = array();
        $keys = $this->getKeys();
        if (array_key_exists($this->getPostId(),$keys)) {
            $key = $keys[$this->getPostId()];
        }
        return $key;
    }

    /**
     * Get session saved keys
     *
     * @return array|bool
     */
    public function getKeys()
    {
        $keys = array();
        if ($this->getInstance()->hasData('preview_keys') == true) {
            $keys = $this->getInstance()->getData('preview_keys');
        }
        return $keys;
    }

}