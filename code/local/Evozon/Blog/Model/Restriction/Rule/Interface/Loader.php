<?php
/**
 * Restriction rules loader and serializer interface
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Restriction_Rule_Interface_Loader
{
    /**
     * Return the rule's object in an array form
     *
     * @return mixed
     */
    public function asArray();

    /**
     * Load a restriction object from an array source
     *
     * @param array $source
     * @return mixed
     */
    public function loadArray($source);
}