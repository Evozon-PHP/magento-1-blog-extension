<?php

/**
 * Container for the data dependencies
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Util_ResolverContainer
    extends Varien_Object
    implements Evozon_Blog_Model_Restriction_Util_Interface_ResolverContainer
{

    /**
     * Protected dependencies.
     * Used for securing that certain dependencies do not get overwritten
     *
     * @var array
     */
    protected $_protectedResolvers = array();

    /**
     * {@inheritdoc}
     *
     * @param Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $resolver
     * @param bool|false                                                       $protected
     *
     * @return $this
     */
    public function addResolver(Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver $resolver, $protected = false)
    {
        if ($this->hasData($resolver->getName()) && $this->isProtectedResolver($resolver->getName())) {
            return $this;
        }

        $this->setData($resolver->getName(), $resolver);

        if ($protected || $resolver->getIsProtected()) {
            $this->_protectedResolvers[$resolver->getName()] = $resolver;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     * @param string $name
     */
    public function removeDataResolver($name = null)
    {
        if ($this->hasData($name)) {
            $this->unsetData($name);
        }
    }

    /**
     * {@inheritdoc}
     * @param string $name
     *
     * @return bool
     */
    public function hasDataResolver($name = null)
    {
        return $this->hasData($name);
    }

    /**
     * {@inheritdoc}
     * @return mixed
     */
    public function getResolvers()
    {
        return $this->getData();
    }

    /**
     * Add multiple data resolvers to the collection
     *
     * @param array $resolvers
     *
     * @return $this
     */
    public function setResolverCollection($resolvers = array())
    {
        foreach ($resolvers as $resolverObj) {
            $this->addResolver($resolverObj);
        }

        return $this;
    }

    /**
     * Resolve a request by iterating through the registered
     * data request resolvers
     *
     * @param mixed $request
     *
     * @return mixed|null Since booleans can be a resolvers return value
     * we return NULL in case of issues
     */
    public function resolveRequest($request)
    {
        if (!is_string($request)) {
            return null;
        }

        $response = reset($this->_data);

        while($response instanceof Evozon_Blog_Model_Restriction_Util_Interface_DataRequestResolver) {
            $response = $response->resolveRequest($request, $this->nextResolver());
        }

        return $response;
    }

    /**
     * Return the next resolver from the collection
     *
     * @return mixed
     */
    public function nextResolver()
    {
        return next($this->_data);
    }

    /**
     * Check if the requested resolver is registered as protected
     *
     * @param string $name
     *
     * @return bool
     */
    public function isProtectedResolver($name)
    {
        return (bool)isset($this->_protectedResolvers[$name]);
    }
}