<?php
/**
 * Restriction rule to deny access to post for specific action
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Rule_PostFullFromAction
    extends Evozon_Blog_Model_Restriction_Rule_Abstract_Rule
    implements Evozon_Blog_Model_Restriction_Rule_Interface_Rule
{
    /**
     * Constants for blog post related actions
     */
    const POST_ACTION_OPTION_LIST = 'listing';
    const POST_ACTION_OPTION_VIEW = 'view';

    /**
     * {@inheritdoc}
     * @var array
     */
    protected $_required = array('action_option');

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_renderer        = 'evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_rule_postFullFromAction';

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_commentRenderer = "evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_comment_rule_postFullFromAction";


    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this
            ->setType('evozon_blog/restriction_rule_postFullFromAction')
            ->setActionOption(self::POST_ACTION_OPTION_VIEW);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function asArray()
    {
        $rules = parent::asArray();
        $rules['action_option']              = $this->getActionOption();

        return $rules;
    }

    /**
     * Retrieve the actions options
     *
     * @return array
     */
    public function getActionsOptions()
    {
        return array(
            self::POST_ACTION_OPTION_LIST => Mage::helper('evozon_blog')->__('listing'),
            self::POST_ACTION_OPTION_VIEW => Mage::helper('evozon_blog')->__('view'),
        );
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function validate()
    {
        $isValid      = true;
        $dataVal      = false;
        $dataResolver = $this->getResolverContainer();

        foreach ($this->_required as $reqValue) {
            $dataVal = $dataResolver->resolveRequest($reqValue);
            if(null !== $dataVal && ($this->getData($reqValue) === $dataVal)) {
                $this->preparePostObject($this->getResolverContainer()->resolveRequest('post_object'));
                $isValid = false;
            }
        }

        return $isValid;
    }

    /**
     * Prepare the Post object in case the post is restricted for set action
     *
     * @param object $postObj
     */
    protected function preparePostObject($postObj)
    {
        if (!isset($postObj) || !$postObj instanceof Evozon_Blog_Model_Post) {
            return;
        }
        
        $postObj->setStoreVisibility(Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_DISABLED);
        $postObj->setIsRestricted(true);
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