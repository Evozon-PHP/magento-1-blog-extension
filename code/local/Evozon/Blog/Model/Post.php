<?php

/**
 * Post model.
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Post extends Mage_Catalog_Model_Product
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_post';

    /**
     * Cache tag
     */
    const CACHE_TAG = 'evozon_blog_post';

    /*
     * Post visibility is enabled
     */
    const EVOZON_BLOG_POST_VISIBILITY_ENABLED = 1;

    /**
     * Post visibility is disabled
     */
    const EVOZON_BLOG_POST_VISIBILITY_DISABLED = 2;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'evozon_blog_post';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'post';

    /**
     * author data
     *
     * @var Evozon_Blog_Model_Author_Interface_IAuthor 
     */
    protected $_author;

    /**
     * owner data
     *
     * @var Evozon_Blog_Model_Author_Interface_IAuthor
     */
    protected $_owner;

    /**
     * @var Evozon_Blog_Restriction_Service_Validate
     */
    protected $_restrictionValidator = null;

    /**
     * Design attributes for post
     * @var array
     */
    protected $_designAttributes = array();

    /**
     * Initialize resources
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('evozon_blog/post');
    }

    /**
     * Get cache tags associated with object id
     * 
     * @TODO implement correct caching invalidation
     * @return array
     */
    public function getCacheIdTagsWithCategories()
    {
        $tags = $this->getCacheTags();
        $affectedCategoryIds = $this->_getResource()->getCategoryIdsWithAnchors($this);

        foreach ($affectedCategoryIds as $categoryId) {
            $tags[] = Mage_Catalog_Model_Category::CACHE_TAG . '_' . $categoryId;
        }
        return $tags;
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }

        return Mage::app()->getStore()->getId();
    }

    /**
     * Getting design attributes code
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getDesignAttributes()
    {
        if (empty($this->_designAttributes)) {
            $this->_designAttributes = Mage::getResourceModel('evozon_blog/attribute')
                ->getAttributeNameByGroup('Custom Design');
        }

        return $this->_designAttributes;
    }

    /**
     * Accessing design settings used to create the layout
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Varien_Object
     */
    public function getDesignSettings()
    {
        $settings = new Varien_Object;
        try {
            $model = Mage::getModel('evozon_blog/design', array($this));
            $settings = $model->getCustomDesignSettings();

            $model->applyCustomDesign();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }

        return $settings;
    }

    /**
     * More data is prepared by the backend model of the attributes
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    public function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->isObjectNew()) {
            $this->setUrlStructure($this->getUrlModel()->getSelectedUrlFormat());
        }
        
        $url = $this->getUrlKey();
        if (empty($url))
        {
            $this->setUrlKey($this->getUrlModel()->formatUrlKey($this->getTitleForUrlKey()));
        }

        $this->_setCreatedAtByPublishDate();

        return $this;
    }

    /**
     * Save data into schedule table if the selected status is pending
     * it will add data about the current post
     * Saves additional relations (websites, categories, product, posts, tags, etc)
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    public function _afterSave()
    {        
        if ($this->getStatus() == Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PENDING) {
            $this->setScheduler();
        }

        if ($this->hasRelatedPosts()) {
            Mage::getModel('evozon_blog/post_relations_related')->setPost($this)->saveRelations();
        }
        
        if ($this->hasCategoryIds()) {
            Mage::getModel('evozon_blog/post_relations_category')->setPost($this)->saveRelations();
        }
        
        if ($this->hasRelatedProducts()) {
            Mage::getModel('evozon_blog/post_relations_product')->setPost($this)->saveRelations();
        }
        
        Mage::getModel('evozon_blog/post_relations_website')->setPost($this)->saveRelations();
        Mage::getModel('evozon_blog/post_relations_tag')->setPost($this)->saveRelations();
        
        return parent::_afterSave();
    }

    /**
     * Set data to scheduler
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function setScheduler()
    {
        $scheduler = Mage::getModel('evozon_blog/scheduler')->init($this);

        try {
            $scheduler->save();
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
    }

    /**
     * Get collection instance
     *
     * @return object
     */
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection();
        $collection->setStoreId($this->getStoreId());

        if (!Mage::app()->getStore()->isAdmin()) {
            $this->_addRestrictedPostsFilter($collection);
        }

        return $collection;
    }

    /**
     * Setting filters and select attributes for the post collection displayed on the listing action
     *
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function getCollectionForListing()
    {
        $collection = $this->getResourceCollection();
        $collection->addAttributeToSelect(array(
                'store_visibility', 'title', 'post_content',
                'short_content', 'publish_date',  'status', 'archive_status',
                'admin_id', 'author_email', 'author_firstname', 'author_lastname',
                'image', 'small_image', 'thumbnail', 'restriction_id',
                'comment_count'
                )
            )
            ->joinAttribute(
                'publish_date', 'evozon_blog_post/publish_date', 'entity_id', null, 'inner', $this->getStoreId()
            )
            ->addWebsitesFilter((int) Mage::app()->getWebsite()->getId())
            ->addFrontendVisibilityFilters()
            ->addAdminJoin()
            ->addCommentCountData()
            ->addRequestPaths();

        return $collection;
    }

    /**
     * Get posts category collection instance
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return object
     */
    public function getCategoryCollection()
    {
        return Mage::getSingleton('evozon_blog/post_relations_category')->getCollectionByPostId($this->getId());
    }

    /**
     * Get posts tags collection instance
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return object
     */
    public function getTagsCollection()
    {
        return Mage::getSingleton('evozon_blog/post_relations_tag')->getCollectionByPostId($this->getId());
    }

    /**
     * Get posts products collection instance
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return object
     */
    public function getProductsCollection()
    {
        return Mage::getSingleton('evozon_blog/post_relations_product')->getCollectionByPostId($this->getId());
    }

    /**
     * Retrieve default attribute set id
     *
     * @access public
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Retrieve post websites identifiers
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        if (!$this->hasWebsiteIds()) {
            $ids = Mage::getSingleton('evozon_blog/post_relations_website')->getIdsByPostId($this->getId());
            $this->setWebsiteIds($ids);
        }
        
        return $this->getData('website_ids');
    }

    /**
     * Get all store ids where post is presented
     *
     * @return array
     */
    public function getStoreIds()
    {
        if (!$this->hasStoreIds()) {
            $storeIds = array();
            if ($websiteIds = $this->getWebsiteIds()) {
                foreach ($websiteIds as $websiteId) {
                    $websiteStores = Mage::app()->getWebsite($websiteId)->getStoreIds();
                    $storeIds = array_merge($storeIds, $websiteStores);
                }
            }
            $this->setStoreIds($storeIds);
        }

        return $this->getData('store_ids');
    }

    /**
     * Getting request path set by a join on the collection
     * If no join has been made/the data is missing, the  url model will be used
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getRequestPath()
    {
        if (!$this->hasData('request_path')) {
            $requestPath = $this->getUrlModel()->setPost($this)->getRequestPath();
            $this->setData('request_path', $requestPath);
        }

        return $this->_getData('request_path');
    }

    /**
     * Get the url to the post details page
     *
     * @access public
     * @return string
     */
    public function getPostUrl()
    {
        return Mage::getBaseUrl() . $this->getRequestPath();
    }

    /**
     * If a user chooses not to use default value of the url_key, it has to  be made from the title
     * @return array|bool
     */
    public function getTitleForUrlKey()
    {
        $title = $this->getTitle();
        return empty($title) ? $this->getAttributeDefaultValue('url_key') : $title;
    }

    /**
     * Set assigned category IDs array to post
     *
     * @param array|string $ids
     * @return Evozon_Blog_Model_Post
     */
    public function setCategoryIds($ids)
    {
        foreach ($ids as $i => $v) {
            if (empty($v)) {
                unset($ids[$i]);
            }
        }
        $this->setData('category_ids', $ids);

        return $this;
    }

    /**
     * Retrieve assigned category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = Mage::getSingleton('evozon_blog/post_relations_category')->getIdsByPostId($this->getId());
            $this->setData('category_ids', $ids);
        }

        return (array) $this->_getData('category_ids');
    }

    /**
     * return an array with related posts to current post.
     * 
     * @see Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Related::_getSelectedRelatedPostFromObject
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */
    public function getSelectedRelatedPosts()
    {
        if (!$this->hasData('related_posts')) {
            $ids = Mage::getSingleton('evozon_blog/post_relations_related')->getIdsByPostId($this->getId());
            $this->setData('related_posts', $ids);
        }

        return (array) $this->_getData('related_posts');
    }

    /**
     * Get selected products array
     *
     * @access public
     * @return array
     */
    public function getSelectedRelatedProducts()
    {
        if (!$this->hasData('related_products')) {
            $ids = Mage::getSingleton('evozon_blog/post_relations_product')->getIdsByPostId($this->getId());
            $this->setData('related_products', $ids);
        }

        return (array) $this->getData('related_products');
    }

    /**
     * Returns an array of ids related to given post
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getSelectedRelatedTags()
    {
        if (!$this->hasData('related_tags')) {
            $ids =Mage::getSingleton('evozon_blog/post_relations_tag')->getIdsByPostId($this->getId(), $this->getStoreId());
            $this->setData('related_tags', $ids);
        }

        return (array) $this->getData('related_tags');
    }

    /**
     * publish_time is not a separate column in database, but it is used separatly
     *   as form field. Set the proper time taken from publish_date.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */
    public function getProperPublishTime()
    {
        $time = array_fill(0, 3, '00');
        if ($this->getId() && $this->getPublishDate()) {
            $data = explode(' ', $this->getPublishDate());
            if (!empty($data[1])) {

                list($hour, $minute, $second) = explode(':', $data[1]);
                $time = array($hour, $minute, $second);
            }
        }

        return $time;
    }

    /**
     * Getting owner model
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Author_Interface_IAuthor
     */
    public function getOwner()
    {
        if (!$this->_owner) {
            $this->_owner = Mage::getModel('evozon_blog/author', $this->getOwnerFields())
                ->getAuthor();
        }

        return $this->_owner;
    }

    /**
     * Return author model
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Author_Interface_IAuthor
     */
    public function getAuthor()
    {
        if (!$this->hasData('author')) {
            $author = Mage::getModel('evozon_blog/author', $this->getAuthorFields())
                ->getAuthor();
            $this->setData('author', $author);
        }

        return $this->getData('author');
    }

    /**
     * Access the resource to fetch into model author details
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getAuthorFields()
    {
        $author = array(
            'author_email' => $this->getAuthorEmail(),
            'author_firstname' => $this->getAuthorFirstname(),
            'author_lastname' => $this->getAuthorLastname()
        );

        if (!array_filter($author)){
            return $this->getOwnerFields();
        }

        return array_merge($author, array('admin_id' => $this->getAdminId()));
    }

    /**
     * Accessing the resource to get data from admin tables
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getOwnerFields()
    {
        return $this->_getResource()->getOwner($this);
    }

    /**
     * Return the comments number of the current post 
     * The comments have FE visibility filters applied
     * Function used in FE
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int
     */
    public function getCommentCount()
    {
        if (!$this->hasData('comment_count')) {
            $commentPostCount = Mage::getResourceModel('evozon_blog/comment')
                ->getFilteredCountByPost($this->getId());

            $this->setData('comment_count', intval($commentPostCount['comment_count']));
        }

        return $this->getData('comment_count');
    }

    /**
     * Checking if the post has any comments
     * It is used in BE in Evozon_Blog_Block_Adminhtml_Post_Edit_Tabs to decide if the comments tab should be displayed
     * It will show all comments for required post, without having any visibility filters applied
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return boolean
     */
    public function hasComments()
    {
        //the returned result is an array with just one element, which is 0 in case there are no comments
        $commentCount = Mage::getResourceModel('evozon_blog/comment')
            ->getCountByPost($this->getId());

        if (intval($commentCount['comment_count']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Return the comments for the current post 
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getComments($parent = 0)
    {
        $commentsCollection = Mage::getModel('evozon_blog/comment')->getCollection();
        $commentsCollection
            ->addFieldToSelect(array(
                'subject', 'content', 'store_id', 'created_at',
                'user_id', 'admin_id', 'author', 'level', 'parent_id')
            )
            ->addCustomerAndAdminJoin()
            ->addFieldToFilter('enabled', array('eq' => true))
            ->addFieldToFilter('post_id', $this->getId())
            ->addFieldToFilter('parent_id', $parent)
            ->addFieldToFilter('status', Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED);

        $commentsCollection->getSelect()->order('created_at DESC');

        return $commentsCollection;
    }

    /**
     * If the short content is defined return it else truncate the post content
     * and return the result
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string  (the truncated content)
     */
    public function generateShortContent($limit)
    {
        if (trim($this->getShortContent())) {
            return $this->getShortContent();
        }

        return $this->getTruncatedContent($limit);
    }

    /**
     * Return the truncated content
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $limit
     * @return string
     */
    public function getTruncatedContent($limit)
    {
        $helper = Mage::helper('evozon_blog');

        return $helper->truncateTextByWords(
                $helper->getContentPlainText($this->getPostContent()), $limit
        );
    }

    /**
     * Get post url model
     *
     * @author Szegedi Szilard <szilard.szegedi@evozon.com>
     * @return Mage_Catalog_Model_Product_Url
     */
    public function getUrlModel()
    {
        if ($this->_urlModel === null) {
            $this->_urlModel = Mage::getSingleton('evozon_blog/factory')->getPostUrlInstance();
        }

        return $this->_urlModel;
    }
    
     /**
     * Check availability display product in category
     *
     * @param   int $categoryId
     * @return  bool
     */
    public function canBeShownInCategory($categoryId)
    {
        return Mage::getSingleton('evozon_blog/post_relations_category')->canBeShownInCategory($this, $categoryId);
    }

    /**
     * Remove url rewrites for deleted post item
     *
     * @return Evozon_Blog_Model_Post
     */
    public function _beforeDelete()
    {
        parent::_beforeDelete();
        Mage::getSingleton('evozon_blog/post_relations_tag')->setPost($this)->updateTagCountOnDelete();

        return $this;
    }

    /**
     * Init indexing process after post delete commit
     *
     * @return Evozon_Blog_Model_Post
     */
    protected function _afterDeleteCommit()
    {
        parent::_afterDeleteCommit();

        if (!$this->hasRewriteToBeRemoved())
        {
            Mage::getSingleton('index/indexer')
                ->processEntityAction($this, self::ENTITY, Mage_Index_Model_Event::TYPE_DELETE);
        }
    }

    /**
     * Callback function which called after transaction commit in resource model
     * It won`t call the reindexing process if a massaction has triggered the post save
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Mage_Catalog_Model_Product
     */
    public function afterCommitCallback()
    {
        if (!$this->hasMassActionReindexed()) {
            Mage::getSingleton('index/indexer')
                ->processEntityAction($this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        }

        parent::afterCommitCallback();
    }

    /**
     * Retrive attributes for post gallery
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getMediaAttributes()
    {
        if (!$this->hasMediaAttributes()) {
            $mediaAttributes = array();
            foreach ($this->getAttributes() as $attribute) {
                if ($attribute->getFrontend()->getInputType() == 'media_image') {
                    $mediaAttributes[$attribute->getAttributeCode()] = $attribute;
                }
            }
            $this->setMediaAttributes($mediaAttributes);
        }

        return $this->getData('media_attributes');
    }

    /**
     * Return the post images for widget gallery
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getImages($storeId)
    {
        return $this->_getResource()->getImages($this->getEntityId(), $storeId);
    }

    /**
     * Return the post images for frontend gallery by Image IDs and Store ID
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getGalleryImages($imageIds, $storeId)
    {
        return $this->_getResource()->getGalleryImages($imageIds, $storeId);
    }

    /**
     * @param Varien_Data_Collection_Db $collection
     * @return Varien_Data_Collection_Db
     */
    public function _addRestrictedPostsFilter(Varien_Data_Collection_Db $collection)
    {
        $restrictedPostIds = array();
        $restrictionValidator = Mage::getModel('evozon_blog/restriction_service_validate', null);
        $restrictionRules = Mage::getResourceModel('evozon_blog/restriction_collection');
        $actionResolver = Mage::getModel(
                'evozon_blog/restriction_util_resolver_actionOption', array(
                'current_action' => Evozon_Blog_Model_Restriction_Rule_PostActionToComponent::POST_ACTION_OPTION_LIST
                )
            )
            ->setIsProtected(true);

        if (!$restrictionRules->count()) {
            return $collection;
        }

        foreach ($restrictionRules as $rule) {
            if ($rule instanceof Evozon_Blog_Model_Restriction) {
                $rule->getRuleSet()->getResolverContainer()->addResolver($actionResolver);
            }

            $isValid = $restrictionValidator->setRules($rule)->validate();
            if (!$isValid) {
                $restrictedPostIds[] = $rule->getPostId();
            }
        }

        if (!empty($restrictedPostIds)) {
            $collection->addFieldToFilter('entity_id', array('nin' => $restrictedPostIds));
        }

        return $collection;
    }

    /**
     * Retrieve the restrictions object for the current post
     *
     * @return Evozon_Blog_Model_Restriction
     */
    public function getRestrictions()
    {
        if (!$this->hasRestrictions()) {
            $restrictions = Mage::getModel('evozon_blog/restriction');
            $restrictions->load($this->getId(), 'post_id');
            $this->setData('restrictions', $restrictions);
        }

        return $this->getData('restrictions');
    }

    /**
     * Add data source for the restrictions after model load
     */
    protected function _afterLoad()
    {
        if (!$this->getId())
        {
            return $this;
        }

        $rules = $this->getRestrictions();
        if ($rules instanceof Evozon_Blog_Model_Restriction && !Mage::app()->getStore()->isAdmin()) {
            $rules->getRuleSet()->getResolverContainer()->addResolver(
                Mage::getModel('evozon_blog/restriction_util_resolver_post', array(
                    'post' => $this)
                ), true
            );

            $validation = $this->getRestrictionValidator()->setRules($rules)->validate();
            if (!$validation) {
                $this->setIsRestricted(true);
            }
        }

        $this->setData('author', $this->getAuthor());
        $this->setData('website_ids', $this->getWebsiteIds());
    }

    /**
     * Return a restriction validator
     *
     * @return false|Mage_Core_Model_Abstract|null
     */
    public function getRestrictionValidator()
    {
        if (null === $this->_validator) {
            $this->_restrictionValidator = Mage::getModel('evozon_blog/restriction_service_validate');
        }

        return $this->_restrictionValidator;
    }

    /**
     * Return Module EntityType
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    static public function getEntityType()
    {
        return Mage::getSingleton('eav/config')->getEntityType(self::ENTITY);
    }

    /**
     * Verify if the post in visible on a specific website
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $website
     * @return bool
     */
    public function isVisibleOnWebsite($website)
    {
        if (Mage::app()->isSingleStoreMode())
        {
            return true;
        }

        return Mage::getResourceModel('evozon_blog/post_relations_website')->hasRelationToPost((int) $this->getId(), $website);
    }

    /**
     * Update createdAt based on publish date
     */
    protected function _setCreatedAtByPublishDate() {
        if ($this->getPublishDate()) {
            $newPublishDate = new DateTime($this->getPublishDate());
            $postCreatedAt =  new DateTime($this->getCreatedAt());

            if ($newPublishDate < $postCreatedAt) {
                $this->setCreatedAt($this->getPublishDate());
            }
        }
    }
}
