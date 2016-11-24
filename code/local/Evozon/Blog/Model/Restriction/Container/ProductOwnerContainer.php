<?php
/**
 * Restriction rules container for user groups
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Container_UserGroupContainer
    extends Evozon_Blog_Model_Restriction_Container_Abstract_Container
    implements Evozon_Blog_Model_Restriction_Container_Interface_Container
{
    /**
     * Cache var for defined user groups
     *
     * @var array
     */
    protected $_cachedUserGroups = array();

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_renderer         = 'evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_container_userGroup';

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_commentRenderer  = "evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_comment_container_userGroup";

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->getUserGroupChoices();
        $defaultGroup = reset($this->_cachedUserGroups);

        $this
            ->setType('evozon_blog/restriction_container_userGroupContainer')
            ->setData($this->getPrefix(), array())
            ->setUserGroupOption($defaultGroup['value']);

        $this->_inputType = 'select';
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function validate()
    {
        $isValid     = !self::DEFAULT_RESTRICTION_RESPONSE;
        $userGroupId = $this->getResolverContainer()->resolveRequest('user_group_id');

        if (!$this->hasData($this->getPrefix()) || !($rules = $this->getData($this->getPrefix()))) {
            return self::DEFAULT_RESTRICTION_RESPONSE;
        }

        if (false === $this->validateContainerRule($userGroupId)) {
            return true;
        }

        foreach ($rules as $restrictionRule) {
            $isValid = (bool)$restrictionRule->validate();

            if (false === $isValid) {
                break;
            }
        }

        return $isValid;
    }

    /**
     * Validate the container rule.
     * If the container rule is true then proceed with checking sub-rules
     * otherwise exit
     *
     * @param $userGroupId
     *
     * @return bool
     */
    protected function validateContainerRule($userGroupId)
    {
        switch ($this->getOperator()) {
            case '==':
                $isValid = $userGroupId == (int)$this->getUserGroupOption();
                break;

            case '!=':
                $isValid = $userGroupId != (int)$this->getUserGroupOption();
                break;

            default:
                $isValid = false;
        }

        return (bool)$isValid;
    }

    public function setResolverContainer(Evozon_Blog_Model_Restriction_Util_Interface_ResolverContainer $container)
    {
        $container->addResolver(
            Mage::getModel('evozon_blog/restriction_util_resolver_userGroupId')
        );

        parent::setResolverContainer($container);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function asArray()
    {
        $rules = parent::asArray();
        $rules['operator']          = $this->getOperator();
        $rules['user_group_option'] = $this->getUserGroupOption();

        return $rules;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function loadArray($source, $key = 'restriction')
    {
        parent::loadArray($source);

        $this->setOperator(isset($source['operator']) ? $source['operator'] : $this->getOperator());
        $this->setUserGroupOption(isset($source['user_group_option'])
            ? $source['user_group_option'] : $this->getUserGroupOption());
    }

    /**
     * Retrieve the user group element options
     *
     * @return array
     */
    public function getUserGroupChoices()
    {
        if (empty($this->_cachedUserGroups)) {
            $groupOptions = array();
            $userGroups   = (array)Mage::getModel('customer/group')->getResourceCollection()->toOptionArray();

            array_walk($userGroups, function($info) use (&$groupOptions) {
                $groupOptions[$info['value']] = $info;
            });

            if (null === $this->getUserGroupOption() || !in_array($this->getUserGroupOption(), array_keys($groupOptions))) {
                $firstOption = reset($groupOptions);
                $this->setUserGroupOption($firstOption['value']);
            }

            $this->_cachedUserGroups = $groupOptions;
        }

        return $this->_cachedUserGroups;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getCommentRendererName()
    {
        return $this->_commentRenderer;
    }

    /**
     * Return a block name string used for rendering the rule for the admin section
     *
     * @return string
     */
    public function getRendererName()
    {
        return $this->_renderer;
    }
}