<?php

/**
 * Data dependency resolver for the action option
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Util_Resolver_ActionOption
    implements Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver
{
    /**
     * Set the current action
     *
     * @var string
     */
    private $_currentAction;

    /**
     * Set the protected flag
     *
     * @var bool
     */
    private $_isProtected = false;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct($params)
    {
        if (isset($params['current_action'])) {
            $this->_currentAction = $params['current_action'];
        }
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName()
    {
        return 'action_option';
    }

    /**
     * {@inheritdoc}
     * @param mixed                                                            $request
     * @param Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $nextResolver
     * @param array                                                            $params
     *
     * @return Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver|string
     */
    public function resolveRequest($request, $nextResolver, $params = array())
    {
        if ($request === $this->getName() && isset($this->_currentAction)) {
            return $this->_currentAction;
        }

        return $nextResolver;
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function getIsProtected()
    {
        return $this->_isProtected;
    }

    /**
     * {@inheritdoc}
     * @param bool $protected
     *
     * @return $this
     */
    public function setIsProtected($protected)
    {
        $this->_isProtected = $protected;

        return $this;
    }
}