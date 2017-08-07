<?php
/**
 * Post Renderer
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2017 Evozon (http://www.evozon.com)
 * @author     Murgocea Victorita <victorita.murgocea@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid_Post_Renderer
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renderer for select action in grid (View|View In Post)
     *
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $viewInPostUrl  = Mage::helper("adminhtml")->getUrl('*/blog_post/edit', ['id' => $row->getPostId(), 'active_tab' => 'comments']);
        $editCommentUrl = Mage::helper('adminhtml')->getUrl('*/*/edit',['id' => $row->getId()]);

        $html = '<select onchange="window.location.href = this.value">';
        $html .= '<option value="">--Select--</option>';
        $html .= '<option value="'.$editCommentUrl.'">Edit</option>';
        $html .= '<option value="'.$viewInPostUrl.'">View In Post</option>';

        $html .= '</select>';

        return $html;


    }
}
