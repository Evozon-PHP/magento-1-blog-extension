<?php
/**
 * Abstract class for container common methods
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Restriction_Container_Abstract_Container
    extends Evozon_Blog_Model_Restriction_Rule_Abstract_Common
{
    /**
     * Default operators
     *
     * @var array
     */
    protected $_defaultOperatorOptions      = null;

    /**
     * Default operators based on input type
     *
     * @var array
     */
    protected $_defaultOperatorInputByType  = null;

    /**
     * @var array
     */
    protected $_arrayInputTypes             = null;

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
     * {@inheritdoc}
     * @return array
     */
    abstract public function validate();

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->loadOperatorOptions();

        $options         = $this->getOperatorSelectOptions();
        $defaultOperator = reset($options);

        $this
            ->setData($this->getPrefix(), array())
            ->setOperator($defaultOperator['value']);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function asArray()
    {
        $restrictions = array();
        $rules = parent::asArray();

        foreach ($this->getData($this->getPrefix()) as $rule) {
            $restrictions[] = $rule->asArray();
        }

        $rules['restriction'] = $restrictions;

        return $rules;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function loadArray($source, $key = 'restriction')
    {
        if (!is_array($source) || !isset($source[$key])) {
            return $this;
        }

        foreach ($source[$key] as $restrictionArray) {
            try {
                $restriction = $this->getObjectInstance($restrictionArray['type']);

                if ($restriction) {
                    $this->addRestriction($restriction);
                    $restriction->loadArray($restrictionArray, $key);
                }
            }
            catch (Exception $exc) {
                Mage::logException($exc);
            }
        }

        return $this;
    }

    /**
     * Add a new restriction rule to the container
     *
     * @param Evozon_Blog_Model_Restriction_Interface_Rule $restriction
     *
     * @return $this
     */
    public function addRestriction($restriction)
    {
        $restriction->setRule($this->getRule());
        $restriction->setPrefix($this->getPrefix());
        $restriction->setResolverContainer($this->getResolverContainer());

        $restrictArray = $this->getData($this->getPrefix());
        $restrictArray[] = $restriction;

        if (!$restriction->getId()) {
            $restriction->setId($this->getId() . '--' . sizeof($restrictArray));
        }

        $this->setData($this->getPrefix(), $restrictArray);

        return $this;
    }

    /**
     * @param Evozon_Blog_Model_Restriction_Util_Interface_ResolverContainer $container
     *
     * @return $this
     */
    public function setResolverContainer(Evozon_Blog_Model_Restriction_Util_Interface_ResolverContainer $container)
    {
        $this->setData('resolver_container', $container);

        return $this;
    }

    /**
     * Return the input type
     *
     * @return string
     */
    public function getInputType()
    {
        if (null === $this->_inputType) {
            return 'string';
        }

        return $this->_inputType;
    }

    /**
     * Retrieve the operator select options
     *
     * @return array
     */
    public function getOperatorSelectOptions()
    {
        $type = $this->getInputType();
        $opt = array();
        $operatorByType = $this->getOperatorByInputType();
        foreach ($this->getOperatorOption() as $k => $v) {
            if (!$operatorByType || in_array($k, $operatorByType[$type])) {
                $opt[] = array('value' => $k, 'label' => $v);
            }
        }

        return $opt;
    }

    /**
     * Retrieve the operator name
     *
     * @return mixed
     */
    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    /**
     * Default operator input by type map getter
     *
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        if (null === $this->_defaultOperatorInputByType) {
            $this->_defaultOperatorInputByType = array(
                'string'      => array('==', '!=', '>=', '>', '<=', '<', '{}', '!{}', '()', '!()'),
                'numeric'     => array('==', '!=', '>=', '>', '<=', '<', '()', '!()'),
                'date'        => array('==', '>=', '<='),
                'select'      => array('==', '!='),
                'boolean'     => array('==', '!='),
                'multiselect' => array('{}', '!{}', '()', '!()'),
                'grid'        => array('()', '!()'),
            );
            $this->_arrayInputTypes = array('multiselect', 'grid');
        }

        return $this->_defaultOperatorInputByType;
    }

    /**
     * Default operator options getter
     * Provides all possible operator options
     *
     * @return array
     */
    public function getDefaultOperatorOptions()
    {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = array(
                '=='  => Mage::helper('evozon_blog')->__('is'),
                '!='  => Mage::helper('evozon_blog')->__('is not'),
                '>='  => Mage::helper('evozon_blog')->__('equals or greater than'),
                '<='  => Mage::helper('evozon_blog')->__('equals or less than'),
                '>'   => Mage::helper('evozon_blog')->__('greater than'),
                '<'   => Mage::helper('evozon_blog')->__('less than'),
                '{}'  => Mage::helper('evozon_blog')->__('contains'),
                '!{}' => Mage::helper('evozon_blog')->__('does not contain'),
                '()'  => Mage::helper('evozon_blog')->__('is one of'),
                '!()' => Mage::helper('evozon_blog')->__('is not one of')
            );
        }
        return $this->_defaultOperatorOptions;
    }

    /**
     * Initialize the operators
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption($this->getDefaultOperatorOptions());
        $this->setOperatorByInputType($this->getDefaultOperatorInputByType());

        return $this;
    }

    /**
     * Retrieve an object from the object pool
     *
     * @param $objectType
     *
     * @return mixed
     *
     * @throws Varien_Exception
     */
    protected function getObjectInstance($objectType)
    {
        if (!$model = Mage::objects($objectType)) {
            $model = Mage::getModel($objectType);
            Mage::objects()->save($model, $objectType);
        }

        return clone $model;
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