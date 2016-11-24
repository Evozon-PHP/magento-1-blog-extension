<?php
/**
 * Interface for the restriction rules rule renderer
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
interface Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Interface_Rule
{
    /**
     * Return a string as the rule's comment
     *
     * @return string
     */
    public function getRuleComment();
}