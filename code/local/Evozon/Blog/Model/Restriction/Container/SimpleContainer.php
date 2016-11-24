<?php
/**
 * Simple rules container
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Container_SimpleContainer
    extends Evozon_Blog_Model_Restriction_Container_Abstract_Container
    implements Evozon_Blog_Model_Restriction_Container_Interface_Container
{
    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_renderer        = 'evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_container_simple';

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_commentRenderer = "evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_comment_container_simple";

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setType('evozon_blog/restriction_container_simpleContainer')
            ->setData($this->getPrefix(), array());
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function validate()
    {
        $isValid = !self::DEFAULT_RESTRICTION_RESPONSE;

        if (!$this->hasData($this->getPrefix()) || !($rules = $this->getData($this->getPrefix()))) {
            return self::DEFAULT_RESTRICTION_RESPONSE;
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