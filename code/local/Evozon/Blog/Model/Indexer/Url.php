<?php
/**
 * Evozon Blog Indexer Url class
 * Responsibility for system actions:
 *  - Post save (changed title or  url key)
 *  - Store save (new store creation, changed store group) - require reindex all data
 *  - Store group save (changed root category or group website) - require reindex all data
 *  - Seo config settings change - require reindex all data
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_Url extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'evozon_blog_post_url_match_result';

    /**
     * Data key for post ids that need to be reindexed
     */
    const EVOZON_BLOG_INDEXER_SAVE_ACTION = 'evozon_reindex_save_post';

    /**
     * marks the data received from a mass action event
     */
    const EVOZON_BLOG_INDEXER_MASS_ACTION = 'evozon_reindex_mass_action';

    /**
     * holds the rewrites that must be deleted
     */
    const EVOZON_BLOG_INDEXER_DELETE_ACTION = 'evozon_reindex_delete_post';

    /**
     * Attributes and their values that can trigger changes in the url structure
     * @var array
     */
    protected $_dependentAttributes = array(
        'status' => Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED
    );

    /**
     * Index math: post save, post, store save
     * store group save, config save
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Evozon_Blog_Model_Post::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION
        ),
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Store_Group::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    protected $_relatedConfigSettings = array(
        Mage_Catalog_Helper_Product::XML_PATH_PRODUCT_URL_SUFFIX,
        Evozon_Blog_Model_Post_Url::XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_CUSTOM,
        Evozon_Blog_Model_Post_Url::XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_KEEP_STRUCTURE
    );

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('evozon_blog')->__('Blog Post URL Rewrites');
    }

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('evozon_blog')->__('Index post URL rewrites');
    }

    /**
     * Retrieve resource instance
     *
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('evozon_blog/post_indexer');
    }

    /**
     * Check if event can be matched by process.
     * Overwrote for specific config save, store and store groups save matching
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Mage_Core_Model_Store::ENTITY) {
            $store = $event->getDataObject();
            if ($store && ($store->isObjectNew() || $store->dataHasChangedFor('group_id'))) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            $storeGroup = $event->getDataObject();
            $hasDataChanges = $storeGroup && ($storeGroup->dataHasChangedFor('root_category_id')
                    || $storeGroup->dataHasChangedFor('website_id'));
            if ($storeGroup && !$storeGroup->isObjectNew() && $hasDataChanges) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Config_Data::ENTITY) {
            $configData = $event->getDataObject();
            if ($configData->isValueChanged() && in_array($configData->getPath(), $this->_relatedConfigSettings)) {
                $result = $this->checkIfReindex($event);
            } else {
                $result = false;
            }
        } else {
            $result = parent::matchEvent($event);
        }

        if ($entity == Evozon_Blog_Model_Post::ENTITY) {
            $result = true;
        }
        
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);
        return $result;
    }

    /**
     * The reindex process is triggered only if the "Keep Old Structure" key from changing Post Url Format
     * is set to be no.
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function checkIfReindex(Mage_Index_Model_Event $event)
    {
        $configData = $event->getDataObject();
        $path = $configData->getPath();
        if ($path == Evozon_Blog_Model_Post_Url::XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_KEEP_STRUCTURE)
        {
            return (bool) !$configData->getValue();
        }

        if ($path == Evozon_Blog_Model_Post_Url::XML_PATH_EVOZON_BLOG_CONFIG_FORMAT_POSTURL_CUSTOM)
        {
            $groups = $configData->getGroups();
            return (bool) !$groups['post_url']['fields']['keep_url_structure']['value'];
        }

        return $configData->isValueChanged();
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     * @return $this
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        $entity = $event->getEntity();

        if ($entity == Evozon_Blog_Model_Post::ENTITY)
        {
            switch ($event->getType()) {
                case Mage_Index_Model_Event::TYPE_DELETE:
                    $this->_registerPostDeleteEvent($event);
                    break;
                case Mage_Index_Model_Event::TYPE_SAVE:
                    $this->_registerPostSaveEvent($event);
                    break;
                case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                    $this->_registerPostMassActionEvent($event);
                    break;
            }
        } else {
            $process = $event->getProcess();
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }

    /**
     * Register event during deleting a post
     * All existing rewrites associated with that post must be removed
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerPostDeleteEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getDataObject()->getData();
        if (isset($data['entity_id']))
        {
            $data = array($data['entity_id']);
        }

        $event->addNewData(self::EVOZON_BLOG_INDEXER_DELETE_ACTION, $data);
    }

    /**
     * Register event data during post save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerPostSaveEvent(Mage_Index_Model_Event $event)
    {
        $post = $event->getDataObject();
        $dataChange = $post->dataHasChangedFor('url_key')
            || $post->dataHasChangedFor('title')
            || $post->dataHasChangedFor('website_ids')
            || $post->dataHasChangedFor('publish_date');

        if ($dataChange) {
            $event->addNewData(self::EVOZON_BLOG_INDEXER_SAVE_ACTION, array($post->getId()));
        }
    }

    /**
     * On a mass action, the url reindexer will be triggered in the attribute called upon affects the structure of the url
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerPostMassActionEvent(Mage_Index_Model_Event $event)
    {
        $actionObject = $event->getDataObject();
        $attrData     = $actionObject->getAttributes();
        $reindex   = $this->_attributesChangeUrl($attrData);

        if ($reindex) {
            $event->addNewData(self::EVOZON_BLOG_INDEXER_MASS_ACTION, $actionObject);
        }

        return $this;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        $action = $this->getIndexerAction();
        if (!empty($data['blog_post_url_reindex_all'])) {
            $action->reindex();
            return $this;
        }

        $dataObject = $event->getDataObject();
        if(isset($data[self::EVOZON_BLOG_INDEXER_SAVE_ACTION])) {
            $action->setPost($dataObject)->reindex();
            return true;
        }

        $object = new Varien_Object();
        if (isset($data[self::EVOZON_BLOG_INDEXER_MASS_ACTION]))
        {
            $object->setId($dataObject->getPostIds())
                ->setStoreId($dataObject->getStoreId());
            $action->setPost($object)->reindex();
            return true;

        }

        if (isset($data[self::EVOZON_BLOG_INDEXER_DELETE_ACTION]))
        {
            $object->setId($data[self::EVOZON_BLOG_INDEXER_DELETE_ACTION]);
            $action->setPost($object)->clearUrlRewrites();
            return true;
        }
    }


    /**
     * Checking for each attribute if it changes the url according to known attributes and their required value
     *
     * @param array $attrData
     * @return bool
     */
    protected function _attributesChangeUrl(array $attrData)
    {
        $reindex = false;
        foreach ($attrData as $attrCode=>$value)
        {
            if ($this->_attributeChangesUrl($attrCode, $value))
            {
                $reindex = true;
                break;
            }
        }

        return $reindex;
    }

    /**
     * Check if the changed value of the mass-action attribute affects the url or not
     *
     * @param string $attrCode
     * @param string $value
     * @return bool
     */
    protected function _attributeChangesUrl($attrCode, $value)
    {
        if (array_key_exists($attrCode, $this->_dependentAttributes))
        {
            return $this->_dependentAttributes[$attrCode] == $value ? true : false;
        }

        return false;
    }

    public function reindexAll()
    {
       return $this->getIndexerAction()->reindex();
    }

    /**
     * Indexer action strategy instance
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function getIndexerAction()
    {
        return Mage::getSingleton('evozon_blog/indexer_action');
    }
}
