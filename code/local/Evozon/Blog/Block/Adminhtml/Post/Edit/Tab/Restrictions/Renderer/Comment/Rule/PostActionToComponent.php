<?php
/**
 * Renderer for the restriction of component based on action rule's comment
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Comment_Rule_PostActionToComponent
    extends Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Restrictions_Renderer_Abstract_Comment
{
    /**
     * JS code to attach observer in order to display
     * restriction message block on demand
     *
     * @var string
     */
    protected $jsAfterElement = '<script type="text/javascript">Event.observe(\'%s\', \'%s\', %s);</script>';

    /**
     * JS code to attach observer in order to display
     * restriction message block on demand
     *
     * @var string
     */
    protected $jsToggleBlock  = '(Evozon.Restrictions.toggleMessageBlock).bindAsEventListener(this, %s)';

    /**
     * Return the rule comment
     *
     * @return string
     */
    protected function getComment()
    {
        return 'Restrict post %s to %s<span class="restriction-message-block" %s>: %s</span>';
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
        $restrictionMsgOptions = $model->getRestrictionMessageOptions();
        $postComponentsOptions = (array)$model->getPostComponentsOptions();
        $jsToggleCode          = sprintf(
            $this->jsToggleBlock,
            json_encode(array(
                'expected' => Evozon_Blog_Model_Restriction_Rule_PostActionToComponent::POST_COMPONENT_OPTION_TITLE_MESSAGE
            ))
        );

        return array(
            'action_option' => array(
                'type'       => 'select',
                'value'      => $model->getActionOption(),
                'values'     => $actionOptions ?: array(),
                'value_name' => $actionOptions[$model->getActionOption()],
            ),

            'post_components_option' => array(
                'type'       => 'select',
                'value'      => $model->getPostComponentsOption(),
                'values'     => $postComponentsOptions ?: array(),
                'value_name' => $postComponentsOptions[$model->getPostComponentsOption()],
                'after_element_html' => sprintf(
                    $this->jsAfterElement,
                    sprintf("%s__%s__post_components_option", $model->getPrefix(), $model->getId()),
                    'change',
                    $jsToggleCode
                ),
            ),

            'restriction_message_css' => array(
                'method' => 'getRestrictionMessageBlockContainerCss',
                'value'  => $this->checkDisplayRestrictionMessageElement(),
            ),

            'restriction_message_option' => array(
                'type'       => 'select',
                'value'      => $model->getRestrictionMessageOption(),
                'values'     => $restrictionMsgOptions ?: array(),
                'value_name' => $restrictionMsgOptions[$model->getRestrictionMessageOption()],
            ),
        );
    }

    /**
     * Return the CSS to hide the restriction messages block
     * if needed on first display
     *
     * @param array $optionsData
     *
     * @return string
     */
    protected function getRestrictionMessageBlockContainerCss(array $optionsData = array())
    {
        return isset($optionsData['value']) ? $optionsData['value'] : '';
    }

    /**
     * Check whether we need to display the restrictions message block
     *
     * @return string
     */
    protected function checkDisplayRestrictionMessageElement()
    {
        if (
            $this->getModel()->getPostComponentsOption()
                === Evozon_Blog_Model_Restriction_Rule_PostActionToComponent::POST_COMPONENT_OPTION_TITLE_MESSAGE
        ) {
            return '';
        }

        return 'style="display: none;"';
    }
}