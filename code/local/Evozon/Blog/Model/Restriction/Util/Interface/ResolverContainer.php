<?php

/**
 * Interface for the data dependency manager
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Restriction_Util_Interface_ResolverContainer
{
    /**
     * Add a dependency resolver
     *
     * @param Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $resolver
     * @param bool|false                                                       $protected
     *
     * @return $this
     */
    public function addResolver(Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $resolver, $protected = false);

    /**
     * Remove dependency resolver
     *
     * @param string $name
     *
     * @return mixed
     */
    public function removeDataResolver($name = null);

    /**
     * Check if container has a certain dependency
     *
     * @param string $name
     *
     * @return mixed
     */
    public function hasDataResolver($name = null);

    /**
     * Return the dependency colllection
     *
     * @return mixed
     */
    public function getResolvers();
}