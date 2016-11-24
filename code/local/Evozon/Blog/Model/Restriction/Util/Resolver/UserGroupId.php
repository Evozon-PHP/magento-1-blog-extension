<?php

/**
 * Data dependency resolver for the group id
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Util_Resolver_UserGroupId
    implements Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver
{

    /**
     * Contain the current user group id
     *
     * @var int
     */
    private $_currentGroupId;

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
        if (isset($params['user_group_id'])) {
            $this->_currentGroupId = $params['user_group_id'];
        }

        if (!isset($this->_currentGroupId)) {
            $this->_currentGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName()
    {
        return 'user_group_id';
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
        if ($request === $this->getName() && isset($this->_currentGroupId)) {
            return $this->_currentGroupId;
        }

        return $nextResolver;
    }
}