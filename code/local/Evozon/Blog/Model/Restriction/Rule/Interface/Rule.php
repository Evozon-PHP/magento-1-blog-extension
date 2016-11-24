<?php
/**
 * Restriction rule interface
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Model_Restriction_Rule_Interface_Rule
{
    /**
     * Validate the rule
     *
     * @param Varien_Object $object
     *
     * @return mixed
     */
    public function validate();

    /**
     * Return the rule's comment block renderer
     *
     * @return string
     */
    public function getCommentRendererName();
}