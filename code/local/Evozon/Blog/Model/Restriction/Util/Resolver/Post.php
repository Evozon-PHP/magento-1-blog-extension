<?php

/**
 * Data dependency resolver for the post data request
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Util_Resolver_Post
    implements Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver
{

    /**
     * Set the current post object
     *
     * @var Evozon_Blog_Model_Post
     */
    private $_currentPost;

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
        if (isset($params['post'])) {
            $this->_currentPost = $params['post'];
        }
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName()
    {
        return 'post_object';
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
        if ($request === $this->getName() && isset($this->_currentPost)) {
            return $this->_currentPost;
        }

        return $nextResolver;
    }

    /**
     * Set the post model object
     *
     * @param Evozon_Blog_Model_Post $post
     *
     * @return $this
     */
    public function setPostObject($post)
    {
        $this->_currentPost = $post;

        return $this;
    }
}