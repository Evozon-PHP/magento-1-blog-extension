<?php
/**
 * Abstract class for a restriction rule
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Restriction_Rule_Abstract_Rule
    extends Evozon_Blog_Model_Restriction_Rule_Abstract_Common
{
    /**
     * Required validation fields
     *
     * @var array
     */
    protected $_required = array();

    /**
     * The container's block renderer name
     *
     * @var string
     */
    protected $_renderer        = '';

    /**
     * The container's comment block renderer name
     *
     * @var string
     */
    protected $_commentRenderer = '';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        return $this;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function validate()
    {
        $isValid = false;
        $dataVal = false;
        $dataResolver = $this->getResolverContainer();

        foreach ($this->_required as $reqValue) {
            $dataVal = $dataResolver->resolveRequest($reqValue);
            if(null === $dataVal || ($this->getData($reqValue) === $dataVal)) {
                return $isValid;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getCommentRendererName()
    {
        throw new LogicException('This method requires that $_commentRenderer contains the name of a valid block!');
    }

    /**
     * Return a block name string used for rendering the rule for the admin section
     *
     * @return string
     * @throws Exception
     */
    public function getRendererName()
    {
        throw new LogicException('This method requires that $_renderer contains the name of a valid block!');
    }
}