<?php

/**
 * Factory Url config interface
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Factory_Config_Interface
{

    /**
     * Throw an exception for method not found
     *
     * @param string $methodName
     *
     * @return mixed
     * @throws BadMethodCallException|Exception
     */
    public function methodNotFound($methodName);

    /**
     * Get indexer model
     *
     * @return string
     */
    public function getContextInstance();

    /**
     * Get resource name
     *
     * @return string
     */
    public function getMainTable();

    /**
     * Returning fields required to format the rewrites to
     *
     * @return array
     */
    public function getFieldsToUpdate();
}