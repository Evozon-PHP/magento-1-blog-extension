<?php
/**
 * Interface for a data dependency resolver
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver
{
    /**
     * Return the name of the dependency resolver
     * This will also be used as an identifier
     *
     * @return string
     */
    public function getName();

    /**
     * Resolved a data request
     *
     * @param mixed                                                            $request
     * @param Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $nextResolver
     * @param array                                                            $params
     *
     * @return mixed
     */
    public function resolveRequest($request, $nextResolver, $params = array());

    /**
     * Check if the resolver needs to be protected inside the container
     *
     * @return boolean
     */
    public function getIsProtected();

    /**
     * Set the protected status
     *
     * @param bool $protected
     *
     * @return mixed
     */
    public function setIsProtected($protected);
}