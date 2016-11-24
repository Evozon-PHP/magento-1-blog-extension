<?php
 /**
 * Restrictions validation service
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Service_Validate
    extends Mage_Core_Model_Abstract
{

    /**
     * Class constructor
     *
     * @param Evozon_Blog_Model_Restriction_Rule $rules
     * @param Evozon_Blog_Model_Post $post
     */
    public function __construct($rules = null, Evozon_Blog_Model_Post $post = null)
    {
        if ($rules instanceof Evozon_Blog_Model_Restriction) {
            $this->setRules($rules);
        }

        $this->setPostObject($post);
    }

    /**
     * Setter for the restriction rules
     *
     * @param Evozon_Blog_Model_Restriction $rules
     * @param bool                          $merge
     *
     * @return $this
     */
    public function setRules(Evozon_Blog_Model_Restriction $rules, $merge = false)
    {
        $newRules = $rules;

        if (true === $merge && $this->hasRules()) {
            $newRules = array_merge($this->getRules(), $rules);
        }
        $this->setData('rules', $newRules);

        return $this;
    }

    /**
     * Main validation method
     *
     * @param Varien_Object $object
     * @param array         $params
     *
     * @return mixed
     * @throws Exception
     */
    public function validate()
    {
        Varien_Profiler::start('__EVOZON_BLOG_RESTRICTIONS_VALIDATE_SERVICE__');
        //we do not need to check the restrictions in admin mode
        if (Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        //if we don't have any rules set something went wrong so signal it
        if (!$this->hasRules()) {
            throw Evozon_Blog_Model_Exception_RestrictionFactory::instance(10000);
        }

        try {
            //se up some default data resolvers
            $resolvers = $this->buildDefaultDataResolvers();
            $ruleSet   = $this->getRules()->getRuleSet();

            $ruleSet->getResolverContainer()->setResolverCollection($resolvers);
            Varien_Profiler::stop('__EVOZON_BLOG_RESTRICTIONS_VALIDATE_SERVICE__');

            return $ruleSet->validate();
        } catch (Exception $exc) {
            Mage::logException($exc);
            Varien_Profiler::stop('__EVOZON_BLOG_RESTRICTIONS_VALIDATE_SERVICE__');

            return false;
        }
    }

    protected function buildDefaultDataResolvers()
    {
        return array(
            Mage::getModel('evozon_blog/restriction_util_resolver_actionOption', array(
                'current_action' => Evozon_Blog_Model_Restriction_Rule_PostActionToComponent::POST_ACTION_OPTION_VIEW
            )),
        );
    }

}