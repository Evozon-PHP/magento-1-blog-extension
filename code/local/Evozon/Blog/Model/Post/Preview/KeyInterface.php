<?php
/**
 * Interface
 *
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2017 Evozon (http://www.evozon.com)
 * @author     Murgocea Victorita <victorita.murgocea@evozon.com>
 */
interface Evozon_Blog_Model_Post_Preview_KeyInterface
{
    /**
     * Has preview key
     * @return mixed
     */
    public function hasKey();

    /**
     * Set preview key
     * @param $key
     * @return mixed
     */
    public function setKey($key);

    /**
     * Get preview keys
     * @return mixed
     */
    public function getKeys();

    /**
     * Get preview key
     * @return mixed
     */
    public function getKey();
}