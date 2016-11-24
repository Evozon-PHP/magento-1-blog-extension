<?php
/**
 * Restriction rule for post related action to a
 * a specific component of the post
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Restriction_Rule_PostActionToComponent
    extends Evozon_Blog_Model_Restriction_Rule_Abstract_Rule
    implements Evozon_Blog_Model_Restriction_Rule_Interface_Rule
{
    /**
     * Constants for blog post related actions
     */
    const POST_ACTION_OPTION_LIST = 'listing';
    const POST_ACTION_OPTION_VIEW = 'view';

    /**
     * Constants for post related components
     */
    const POST_COMPONENT_OPTION_TITLE         = 'title';
    const POST_COMPONENT_OPTION_TITLE_PREVIEW = 'title_and_preview';
    const POST_COMPONENT_OPTION_TITLE_MESSAGE = 'title_and_message';

    /**
     * {@inheritdoc}
     * @var array
     */
    protected $_required = array('action_option');

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_renderer        = 'evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_rule_postActionToComponent';

    /**
     * {@inheritdoc}
     * @var string
     */
    protected $_commentRenderer = "evozon_blog/adminhtml_post_edit_tab_restrictions_renderer_comment_rule_postActionToComponent";


    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $options = $this->getRestrictionMessageOptions() ?: array();
        reset($options);

        $this
            ->setType('evozon_blog/restriction_rule_postActionToComponent')
            ->setActionOption(self::POST_ACTION_OPTION_VIEW)
            ->setPostComponentsOption(self::POST_COMPONENT_OPTION_TITLE_PREVIEW)
            ->setRestrictionMessageOption(key($options));
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function validate()
    {
        $isValid      = true;
        $dataVal      = false;
        $dataResolver = $this->getResolverContainer();

        foreach ($this->_required as $reqValue) {
            $dataVal = $dataResolver->resolveRequest($reqValue);
            if(null !== $dataVal && ($this->getData($reqValue) === $dataVal)) {
                $this->updatePostFromRestriction($this->getResolverContainer()->resolveRequest('post_object'));
            }
        }

        return $isValid;
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function asArray()
    {
        $rules = parent::asArray();
        $rules['action_option']              = $this->getActionOption();
        $rules['post_components_option']     = $this->getPostComponentsOption();
        $rules['restriction_message_option'] = $this->getRestrictionMessageOption();

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
     * Retrieve the restriction message blocks
     *
     * @return mixed
     */
    public function getRestrictionMessageOptions()
    {
        $cmsBlocks = Mage::getModel('cms/block')->getCollection();

        foreach ($cmsBlocks as $block) {
            $options[$block->getId()] = $block->getTitle();
        }

        return $options;
    }

    /**
     * Retrieve the post components element options
     *
     * @return array
     */
    public function getPostComponentsOptions()
    {
        return array(
            self::POST_COMPONENT_OPTION_TITLE         => Mage::helper('evozon_blog')->__('only display the title'),
            self::POST_COMPONENT_OPTION_TITLE_PREVIEW => Mage::helper('evozon_blog')->__('display title and preview'),
            self::POST_COMPONENT_OPTION_TITLE_MESSAGE => Mage::helper('evozon_blog')->__('display title and message'),
        );
    }

    /**
     * Update the post according to restriction rule settings
     *
     * @param Evozon_Blog_Model_Post $post
     *
     * @return mixed
     */
    protected function updatePostFromRestriction($post)
    {
        if (!$post instanceof Evozon_Blog_Model_Post) {
            return;
        }

        switch ($this->getPostComponentsOption()) {
            case self::POST_COMPONENT_OPTION_TITLE:
                $restrictedMessage = null;
                break;

            case self::POST_COMPONENT_OPTION_TITLE_MESSAGE:
                $restrictedMessage = $this->getPostRestrictedMessage();
                break;

            case self::POST_COMPONENT_OPTION_TITLE_PREVIEW:
                $restrictedMessage = $post->generateShortContent(
                    (int) $this->getConfigModel()->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_TEASER_WORDS_COUNT)
                );
                break;

            default:
                $restrictedMessage = $post->getPostContent();
                break;
        }

        $post->setPostContent($restrictedMessage);
        $post->setShortContent($restrictedMessage);

        return $post;
    }

    /**
     * Retrieve the restriction message block
     *
     * @return null|string
     */
    protected function getPostRestrictedMessage()
    {
        $message = '';

        if (!$this->hasRestrictionMessageOption()) {
            return null;
        }

        $messageBlock = Mage::getModel('cms/block')->load($this->getRestrictionMessageOption());

        if ($messageBlock instanceof Mage_Cms_Model_Block) {
            $message = $messageBlock->getContent();
        }

        return $message;
    }
    
    /**
     * Return the config model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Config
     */
    public function getConfigModel()
    {
        return Mage::getSingleton('evozon_blog/config');
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