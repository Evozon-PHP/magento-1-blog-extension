<?php
/**
 * Abstract class containing common restriction rules methods
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Restriction_Rule_Abstract_Common
    extends Varien_Object
{
    /**
     * Default restriction response to be used
     */
    const DEFAULT_RESTRICTION_RESPONSE = true;

    /**
     * A pointer to the form object
     *
     * @var Varien_Data_Form
     */
    protected $_form = null;

    /**
     * The html default data input type
     *
     * @var string
     */
    protected $_inputType = 'string';

    /**
     * Class constructor
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * Returns the form object from the rule
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function asArray()
    {
        return array('type' => $this->getType());
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function loadArray($source, $key = 'restriction')
    {
        $this->addData($source);
    }

    /**
     * Return the restriction controls name prefix
     *
     * @return mixed
     */
    public function getPrefix()
    {
        if (!$this->hasData('prefix')) {
            $this->setData('prefix', Evozon_Blog_Model_Restriction::RESTRICTIONS_PREFIX);
        }

        return $this->getData('prefix');
    }
}