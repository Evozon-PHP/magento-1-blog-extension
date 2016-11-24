<?php
/**
 * Restriction model
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction
    extends Mage_Core_Model_Abstract
{

    /**
     * Form container
     *
     * @var Varien_Data_Form
     */
    protected $_form               = null;

    /**
     * Base container model
     *
     * @var string
     */
    protected $_baseContainerModel = 'evozon_blog/restriction_container_simpleContainer';

    protected $_baseResolverContainerModel = 'evozon_blog/restriction_util_resolverContainer';

    /**
     * The restrictions rules
     *
     * @var array
     */
    private   $_ruleSet            = null;

    /**
     * Const to define the fieldset prefix
     */
    const RESTRICTIONS_PREFIX      = 'evozon_post_restrictions';

    /**
     * Fieldset legend
     */
    const FIELDSET_LEGEND              = 'Restriction Rules';

    /**
     * Magento constructor
     */
    public function _construct()
    {
        $this->_init('evozon_blog/restriction');

        $this->setPrefix(self::RESTRICTIONS_PREFIX);
    }

    /**
     * Return the restrictions imposed
     *
     * @return array
     */
    public function getRuleSet()
    {
        if (empty($this->_ruleSet)) {
            $this->_resetRuleSet();
        }

        if ($this->hasRestrictionsSerialized() && false !== $this->getRestrictionsSerialized()) {
            $rules = unserialize($this->getRestrictionsSerialized());

            if (false !== $rules && is_array($rules)) {
                $this->_ruleSet->loadArray($rules);
            }

            $this->unsRestrictionsSerialized();
        }

        return $this->_ruleSet;
    }

    /**
     * Set an array of rules
     *
     * @param array $ruleSet
     *
     * @return $this
     */
    public function setRuleSet($ruleSet)
    {
        $this->_ruleSet = $ruleSet;

        return $this;
    }

    /**
     * Reset or initialize the rules set
     *
     * @param Evozon_Blog_Model_Restriction_Interface_Rule $newRule
     *
     * @return $this
     */
    protected function _resetRuleSet($newRule = null)
    {
        if (null === $newRule) {
            $newRule = $this->getRulesContainerInstance();
        }

        $newRule
            ->setRule($this)
            ->setId('1')
            ->setPrefix($this->getPrefix())
            ->setResolverContainer($this->getResolverContainerInstance());

        $this->setRuleSet($newRule);

        return $this;
    }

    /**
     * Return an instance of a rules container
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function getRulesContainerInstance()
    {
        return Mage::getModel($this->_baseContainerModel);
    }

    public function getResolverContainerInstance()
    {
        return Mage::getModel($this->_baseResolverContainerModel);
    }

    /**
     * Return the current form or init a new one
     *
     * @return Varien_Data_Form
     */
    public function getForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();
        }

        return $this->_form;
    }

    /**
     * Load the restriction rules from a POST
     * This method is used mostly to generate a restriction rules tree
     * before saving it to the db
     *
     * @param array $data
     *
     * @return $this
     */
    public function loadPostData($data, $key = 'restriction_form')
    {
        if (!isset($data[$key])) {
            return;
        }

        //we clear any saved rules before applying those from
        //the form in order to prevent duplication
        if (!$this->isObjectNew()) {
            $this->unsRestrictionsSerialized();
        }

        $arr = $this->_convertFlatToRecursive($data);

        if (isset($arr['restriction'])) {
            $mainRuleset = array_shift($arr['restriction']);
            $this->_resetRuleSet();
            $this->getRuleSet()->setRestrictions(array())->loadArray($mainRuleset);
        }

        return $this;
    }

    /**
     * Convert the restriction rules sent from POST data to
     * a multidimensional array
     *
     * @param array $data
     *
     * @return array
     */
    protected function _convertFlatToRecursive($data, $dataKey = 'restriction')
    {
        $arr = array();

        if (!$data->hasRestrictionForm()) {
            return array();
        }

        foreach ($data->getRestrictionForm() as $id => $restrictionData) {
            $path = explode('--', $id);
            $node =& $arr;

            for ($iter = 0, $length = sizeof($path); $iter < $length; $iter++) {
                if (!isset($node[$dataKey][$path[$iter]])) {
                    $node[$dataKey][$path[$iter]] = array();
                }
                $node =& $node[$dataKey][$path[$iter]];
            }

            foreach ($restrictionData as $k => $v) {
                $node[$k] = $v;
            }
        }

        return $arr;
    }

    /**
     * Save the restriction rules as a serialized array
     * on the post object
     *
     * @param Evozon_Blog_Model_Post $post
     *
     * @return $this
     */
    public function saveRules(Evozon_Blog_Model_Post $post)
    {
        $post->setRestrictionsSerialized(
            serialize($this->getRuleSet()->asArray())
        );

        return $this;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $date = Mage::getModel('core/date')->gmtTimestamp();

        $this->setRestrictionsSerialized(
            serialize($this->getRuleSet()->asArray())
        );

        if ($this->isObjectNew()) {
            $this->setCreatedAt($date);
        }

        $this->setUpdatedAt($date);

        return parent::_beforeSave();
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterLoad()
    {
        if (!$this->hasFieldPrefix()) {
            $this->setPrefix(self::RESTRICTIONS_PREFIX);
        }

        return parent::_afterLoad();
    }

    /**
     * Set the main container model
     *
     * @param string $model
     * @return $this
     */
    public function setBaseRulesContainerModel($model)
    {
        $this->_baseContainerModel = $model;

        return $this;
    }

    public function setResolverContainerInstance($model)
    {
        $this->_baseResolverContainerModel = $model;

        return $this;
    }

    public function __clone()
    {
        $this
            ->unsCreatedAt()
            ->unsUpdatedAt()
            ->unsPostId()
            ->unsRestrictionId();
    }
}