<?php
/**
 * Renderer for the restriction rules user group container's comment
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Comment_Container_ProductOwner
    extends Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Comment
{

    /**
     * Return the rule comment
     *
     * @return string
     */
    public function getComment()
    {
        return 'If the customer is a buyer of %s %s:';
    }

    /**
     * Return the rule comment's options config
     *
     * @return array
     */
    protected function _getCommentOptions()
    {
        $userGroupChoices = $this->getModel()->getUserGroupChoices();

        return array(
            'operator' => array(
                'type'       => 'select',
                'value'      => $this->getModel()->getOperator(),
                'values'     => $this->getModel()->getOperatorSelectOptions() ?: array(),
                'value_name' => $this->getModel()->getOperatorName(),
            ),

            'user_group_option' => array(
                'type'       => 'select',
                'value'      => $this->getModel()->getUserGroupOption(),
                'values'     => $userGroupChoices ?: array(),
                'value_name' => $userGroupChoices[$this->getModel()->getUserGroupOption()]['label'],
            ),
        );
    }
}