<?php
/**
 * Renderer for the restriction rules simple container's comment
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Comment_Container_Simple
    extends Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Comment
{

    /**
     * Return the rule comment
     *
     * @return string
     */
    public function getComment()
    {
        return 'Configure the blog post restrictions: (Leave empty for UNRESTRICTED access)';
    }

    /**
     * Return the rule comment options config
     *
     * @return array
     */
    protected function _getCommentOptions()
    {
        return array();
    }
}