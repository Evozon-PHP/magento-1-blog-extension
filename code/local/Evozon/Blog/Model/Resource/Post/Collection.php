<?php

/**
 * Post Collection
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_post';

    /**
     * Post to website linkage table
     *
     * @var string
     */
    protected $_postWebsiteTable;

    /**
     * Init collection of post objects
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('evozon_blog/post');

        $this->_initTables();
    }

    /**
     * Define post website and category post tables
     *
     */
    protected function _initTables()
    {
        $this->_postWebsiteTable = $this->getResource()->getTable('evozon_blog/post_website');
    }

    /**
     * Specify category filter for post collection
     *
     * @param Mage_Catalog_Model_Category | int $category
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addCategorysFilter($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            $category = $category->getId();
        }

        $this->getSelect()->join(
            array('related_category' => $this->getTable('evozon_blog/post_category')),
            'related_category.post_id = e.entity_id', array('position')
        );
        $this->getSelect()->where('related_category.category_id = ?', (int) $category);

        return $this;
    }

    /**
     * Specify website filter for post collection
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $website
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addWebsitesFilter($website)
    {
        $this->getSelect()->join(
            array('websites' => $this->getTable('evozon_blog/post_website')),
            'websites.post_id = e.entity_id'
        );

        $this->getSelect()->where('websites.website_id = ?', $website);
        return $this;
    }

    /**
     * Adding post website names to result collection
     * Add for each post websites information
     *
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addWebsiteNamesToCollection()
    {
        $postWebsites = array();
        foreach ($this as $post) {
            $postWebsites[$post->getId()] = array();
        }

        if (!empty($postWebsites)) {
            $select = $this->getConnection()->select()
                ->from(array('post_website' => $this->_postWebsiteTable))
                ->join(
                    array('website' => $this->getResource()->getTable('core/website')),
                    'website.website_id = post_website.website_id',
                    array('name')
                )
                ->where('post_website.post_id IN (?)', array_keys($postWebsites))
                ->where('website.website_id > ?', 0);

            $data = $this->getConnection()->fetchAll($select);
            foreach ($data as $row) {
                $postWebsites[$row['post_id']][] = $row['website_id'];
            }
        }

        foreach ($this as $post) {
            if (isset($postWebsites[$post->getId()])) {
                $post->setData('websites', $postWebsites[$post->getId()]);
            }
        }

        return $this;
    }

    /**
     * Overriden function
     *
     * @return boolean
     */
    public function isEnabledFlat()
    {
        return false;
    }

    /**
     * Add filters for frontend post visibility.
     *
     * @author  Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return  \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addFrontendVisibilityFilters()
    {
        $this->addAttributeToFilter('status', Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED);
        $this->addAttributeToFilter('store_visibility', Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED);

        return $this;
    }

    /**
     * Get the data of the author from the admin table
     * (because only the admin can be the author of a post)
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addAdminJoin()
    {
        $this->joinAttribute('admin_id', 'evozon_blog_post/admin_id', 'entity_id', null, 'inner', Mage_Core_Model_App::ADMIN_STORE_ID)
            ->joinField('admin_user_id', 'admin/user', 'user_id', 'user_id=admin_id', null, 'left')
            ->joinField('author_firstname', 'admin/user', 'firstname', 'user_id=admin_id', null, 'left')
            ->joinField('author_lastname', 'admin/user', 'lastname', 'user_id=admin_id', null, 'left')
            ->joinField('author_email', 'admin/user', 'email', 'user_id=admin_id', null, 'left');

        return $this;
    }

    /**
     * Joins comments table and adds comment count to each post individually
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addCommentCountData()
    {
        $commentSelect = Mage::getResourceModel('evozon_blog/comment')->getSelectQueryForPostsCollection($this->getAllIds());

        $this->getSelect()
            ->joinLeft(array('c' => new Zend_Db_Expr('( ' . $commentSelect . ' )')), 'e.entity_id = c.post_id', array('comment_count'));

        return $this;
    }

    /**
     * Loading request_paths for each item
     * This way, the post url will be set on collection display
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addRequestPaths()
    {
        $requestPathsSelect = Mage::getResourceModel(Evozon_Blog_Model_Factory::EVOZON_BLOG_URL_INDEXER)
            ->getSelectQueryForPostsCollection($this->getStoreId());

        $this->getSelect()
            ->joinLeft(array('req' => new Zend_Db_Expr('( ' . $requestPathsSelect . ' )')), 'e.entity_id = req.entity_id', array('request_path'));

        return $this;
    }

    /**
     * Processing collection items after loading
     * Adding url rewrites, etc
     *
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    protected function _afterLoad()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            return $this;
        }

        $this->_loadRestrictions();
        return $this;
    }

    /**
     * Loading restrictions into post collection
     *
     * @author Denis Rendler <denis.rendler@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    protected function _loadRestrictions()
    {
        $validator = Mage::getModel('evozon_blog/restriction_service_validate');
        $postResolver = Mage::getModel('evozon_blog/restriction_util_resolver_post');
        $resolvers = array(
            Mage::getModel('evozon_blog/restriction_util_resolver_actionOption', array(
                'current_action' => Evozon_Blog_Model_Restriction_Rule_PostActionToComponent::POST_ACTION_OPTION_LIST
            ))->setIsProtected(true),&$postResolver
        );

        Varien_Profiler::start('__EVOZON_BLOG_RESTRICTIONS_COLLECTION_AFTER_LOAD__');
        foreach ($this->getItems() as $post) {
            $postResolver->setPostObject($post);
            $restrictions = $post->getRestrictions();

            $subRules = $restrictions->getRuleSet()->getData($restrictions->getPrefix());
            //if we don't have any rules set just continue to the next post
            if (empty($subRules)) {
                continue;
            }

            $restrictions
                ->getRuleSet()
                ->getResolverContainer()
                ->setResolverCollection($resolvers);

            $validator
                ->setRules($restrictions)
                ->validate();
        }
        Varien_Profiler::stop('__EVOZON_BLOG_RESTRICTIONS_COLLECTION_AFTER_LOAD__');

        return $this;
    }

    /**
     * Set the locale dates for posts collection
     * If the function is used from back end the created_at and publish_date will be set,
     * else only the publish_date with the format from system config
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Post_Collection
     */
    public function setProperDateFormat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $this->setProperDateFormatForAdmin();

            return $this;
        }

        $this->setProperDateFormatForCustomer();

        return $this;
    }

    /**
     * Set the proper created at and publish date for the back end
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForAdmin()
    {
        foreach ($this->getItems() as $post) {
            $post->setCreatedAt($this->getLocaleDate($post->getCreatedAt()));
            $post->setPublishDate($this->getLocaleDate($post->getPublishDate()));
        }
    }

    /**
     * Set the proper publish at date for the front end
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForCustomer()
    {
        foreach ($this->getItems() as $post) {
            $post->setPublishDate($this->getTimeByFormatAndLocale($post->getPublishDate()));
        }
    }

    /**
     * Return the date converted in the locale date
     *
     * @TODO   Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getLocaleDate($date) {
        return Mage::helper('evozon_blog')->getLocaleDate($date);
    }

    /**
     * Return the date converted in the locale date and in the default locale from system config
     *
     * @TODO    Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getTimeByFormatAndLocale($date) {
        return Mage::helper('evozon_blog')->getTimeByFormatAndLocale($date);
    }
}