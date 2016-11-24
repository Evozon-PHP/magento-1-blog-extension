<?php

/**
 * Blog Search Fulltext Indexer
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Search_Indexer_Mysql extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'evozon_blog_search_fulltext_match_result';

    /**
     * List of searchable attributes
     *
     * @var null|array
     */
    protected $_searchableAttributes = null;

    /**
     * Retrieve resource instance
     *
     * @return Evozon_Blog_Model_Resource_Search_Indexer_Fulltext
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('evozon_blog/search_indexer_mysql');
    }

    /**
     * Indexer must be match entities
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Evozon_Blog_Model_Post::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
        ),
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    /**
     * Related Configuration Settings for match
     *
     * @var array
     */
    protected $_relatedConfigSettings = array(
        Evozon_Blog_Model_Search_Config::XML_PATH_BLOG_SEARCH_TYPE
    );

    /**
     * Retrieve Fulltext Search instance
     *
     * @return Evozon_Blog_Model_Search_Fulltext
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('evozon_blog/search_engine_mysql');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('evozon_blog')->__(' Blog Search Index');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('evozon_blog')->__('Rebuild Blog post fulltext search index');
    }

    /**
     * Check if event can be matched by process
     *
     * @param Mage_Index_Model_Event $event
     *
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        $data       = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }
        $result = false;
        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            /* @var $attribute Evozon_Blog_Model_Resource_Post_Attribute */
            $attribute      = $event->getDataObject();

            if (!$attribute) {
                $result = false;
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = $attribute->getIsSearchable();
            } else {
                $result = false;
            }
        }
        else if ($entity == Evozon_Blog_Model_Post::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
                $result = true;
            }
        }
        else if ($entity == Mage_Core_Model_Store::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = true;
            } else {
                /* @var $store Mage_Core_Model_Store */
                $store = $event->getDataObject();
                if ($store && $store->isObjectNew()) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        } else if ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            /* @var $storeGroup Mage_Core_Model_Store_Group */
            $storeGroup = $event->getDataObject();
            if ($storeGroup && $storeGroup->dataHasChangedFor('website_id')) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Config_Data::ENTITY) {
            $data = $event->getDataObject();
            if ($data && in_array($data->getPath(), $this->_relatedConfigSettings)) {
                $result = $data->isValueChanged();
            } else {
                $result = false;
            }
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case Evozon_Blog_Model_Post::ENTITY:
                $this->_registerPostEvent($event);
                break;

            case Mage_Catalog_Model_Convert_Adapter_Product::ENTITY:
                $event->addNewData('evozon_blog_search_fulltext_reindex_all', true);
                break;

            case Mage_Core_Model_Config_Data::ENTITY:
            case Mage_Core_Model_Store::ENTITY:
            case Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
                $event->addNewData('evozon_blog_search_fulltext_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
    }

    /**
     * Register data required by post process in event object
     *
     * @param Mage_Index_Model_Event $event
     *
     * @return Evozon_Blog_Model_Search_Indexer_Fulltext
     */
    protected function _registerPostEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                /* @var $post Evozon_Blog_Model_Post */
                $post = $event->getDataObject();
                $event->addNewData('evozon_blog_search_update_post_id', $post->getId());
                break;
            case Mage_Index_Model_Event::TYPE_DELETE:
                /* @var $post Evozon_Blog_Model_Post */
                $post = $event->getDataObject();

                $event->addNewData('evozon_blog_search_delete_product_id', $post->getId());
                break;
            case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                /* @var $actionObject Varien_Object */
                $actionObject = $event->getDataObject();
                $attrData     = $actionObject->getAttributesData();
                $rebuildIndex = false;
                $reindexData  = array();

                // check if force reindex required
                if (isset($attrData['force_reindex_required']) && $attrData['force_reindex_required']) {
                    $rebuildIndex = true;
                    $reindexData['evozon_blog_search_force_reindex'] = $attrData['force_reindex_required'];
                }

                // check if status changed
                if (isset($attrData['status'])) {
                    $rebuildIndex = true;
                    $reindexData['evozon_blog_search_status'] = $attrData['status'];
                }

                // check changed websites
                if ($actionObject->getWebsiteIds()) {
                    $rebuildIndex = true;
                    $reindexData['evozon_blog_search_website_ids'] = $actionObject->getWebsiteIds();
                    $reindexData['evozon_blog_search_type'] = $actionObject->getActionType();
                }

                $searchableAttributes = array();
                if (is_array($attrData)) {
                    $searchableAttributes = array_intersect($this->_getSearchableAttributes(), array_keys($attrData));
                }

                if (count($searchableAttributes) > 0) {
                    $rebuildIndex = true;
                    $reindexData['evozon_blog_search_force_reindex'] = true;
                }

                // register affected products
                if ($rebuildIndex) {
                    $reindexData['evozon_blog_search_post_ids'] = $actionObject->getPostIds();
                    foreach ($reindexData as $k => $v) {
                        $event->addNewData($k, $v);
                    }
                }
                break;
        }

        return $this;
    }

    /**
     * Retrieve searchable attributes list
     *
     * @return array
     */
    protected function _getSearchableAttributes()
    {
        if (is_null($this->_searchableAttributes)) {
            /** @var $attributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
            $attributeCollection = Mage::getResourceModel('evozon_blog/attribute');
            $attributeCollection->addIsSearchableFilter();

            foreach ($attributeCollection as $attribute) {
                $this->_searchableAttributes[] = $attribute->getAttributeCode();
            }
        }

        return $this->_searchableAttributes;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();

        if (!empty($data['evozon_blog_search_fulltext_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['evozon_blog_search_delete_product_id'])) {
            $postId = $data['evozon_blog_search_delete_product_id'];

            $this->_getIndexer()->cleanIndex(null, $postId)
                ->resetSearchResults();
        } else if (!empty($data['evozon_blog_search_update_post_id'])) {
            $postId = $data['evozon_blog_search_update_post_id'];

            $this->_getIndexer()->rebuildIndex(null, $postId)
                ->resetSearchResults();
        } else if (!empty($data['evozon_blog_search_post_ids'])) {
            // mass action
            $postIds = $data['evozon_blog_search_post_ids'];

            if (!empty($data['evozon_blog_search_website_ids'])) {
                $websiteIds = $data['evozon_blog_search_website_ids'];
                $actionType = $data['evozon_blog_search_type'];

                foreach ($websiteIds as $websiteId) {
                    foreach (Mage::app()->getWebsite($websiteId)->getStoreIds() as $storeId) {
                        if ($actionType == 'remove') {
                            $this->_getIndexer()
                                ->cleanIndex($storeId, $postIds)
                                ->resetSearchResults();
                        } else if ($actionType == 'add') {
                            $this->_getIndexer()
                                ->rebuildIndex($storeId, $postIds)
                                ->resetSearchResults();
                        }
                    }
                }
            }
            if (isset($data['evozon_blog_search_status'])) {
                $status = $data['evozon_blog_search_status'];
                if ($status == 1) {
                    $this->_getIndexer()
                        ->rebuildIndex(null, $postIds)
                        ->resetSearchResults();
                } else {
                    $this->_getIndexer()
                        ->cleanIndex(null, $postIds)
                        ->resetSearchResults();
                }
            }
            if (isset($data['evozon_blog_search_force_reindex'])) {
                $this->_getIndexer()
                    ->rebuildIndex(null, $postIds)
                    ->resetSearchResults();
            }
        }
    }

    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->rebuildIndex();
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}
