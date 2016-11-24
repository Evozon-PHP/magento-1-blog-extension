<?php
/**
 * Renderer for the restriction of entire post based on action rule's comment
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Comment_Rule_PostFullFromAction
    extends Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Comment
{

    /**
     * Return the rule comment
     *
     * @return string
     */
    protected function getComment()
    {
        return 'Restrict entire post from %s';
    }

    /**
     * Return the rule comment's options config
     *
     * @return array
     */
    protected function _getCommentOptions()
    {
        $model                 = $this->getModel();
        $actionOptions         = $model->getActionsOptions();

        return array(
            'action_option' => array(
                'type'       => 'select',
                'value'      => $model->getActionOption(),
                'values'     => $actionOptions ?: array(),
                'value_name' => $actionOptions[$model->getActionOption()],
            )
        );
    }
}