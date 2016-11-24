<?php

/**
 * Model for post-tags relations
 * It will check the post status/visibility and it will decide which scenario to apply for updating tag count
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Post_Relations_Tag extends Evozon_Blog_Model_Post_Relations_Abstract
{

    /**
     * Website and Store ids that belong to them
     * @var array
     */
    protected $_storesGroupedByWebsiteIds = array();
    
    /**
     * An array of [tag_id]=>[store_ids where the tag exists on curent post]
     * @var array
     */
    protected $_storesGroupedByTagIds;
    
    /**
     * Array of stores that don`t use default values
     * @var array
     */
    protected $_storesThatDontUseDefaultValues;
    
    /**
     * count updating scenario
     * @var string
     */
    protected $_scenario = null;

    /**
     * Model name
     * @return string
     */
    protected function getModelName()
    {
        return "evozon_blog/post_relations_tag";
    }
    
    protected function getSelectedIdsDataName()
    {
        return 'related_tags';
    }
    
    /**
     * Get collection of tags related to post
     * This is used to display all tags related to a post on listing or on view action
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return \Evozon_Blog_Model_Resource_Tag_Collection
     */
    public function getCollectionByPostId($postId)
    {
        $collection = Mage::getModel('evozon_blog/tag')->getResourceCollection()
            ->addAttributeToSelect(array('name', 'url_key'))
            ->addAttributeToFilter('entity_id', array('in' => $this->getIdsByPostId($postId))
        );

        return $collection;
    }

    /**
     * Delete existent relations between post and tags on the specified store.
     * Insert into linked table the relations between given post and selected tags
     */
    public function saveRelations()
    {
        $this->_extractDataFromPost();
        $this->_updateCount();
        
        $postStoreId = $this->getPost()->getStoreId();
        $storeIds = array($postStoreId);
        if ($postStoreId == 0) {
            $storeIds = Mage::getResourceModel('core/store_collection')->getAllIds();
        }

        $this->_getResource()
            ->setPostId($this->getPost()->getId())
            ->_addStoreDependableRelations($storeIds, $this->getAddedIds())
            ->_removeStoreDependableRelations($storeIds, $this->getDeletedIds());
        
        if ($this->getScenario() == 'incrementSelectedTagsOnSelectedStoresOnNewPost')
        {
            $this->incrementCountOfSelectedTagsOnSelectedStores();
        }
        
        return $this;
    }
    
    /**
     * Getting old ids
     * On the tag model - they have to be selected by the store_id as well
     * @return array
     */
    public function getOldIds()
    {
        if ($this->hasData('old_ids'))
        {
            $oldIds = $this->getIdsByPostId($this->getPost()->getId(), $this->getPost()->getStoreId());
            $this->setData('old_ids', $oldIds);
        }
        
        return $this->getData('old_ids');
    }

    /**
     * Update count based on the post`s status & store visibility scenario
     */
    protected function _updateCount()
    {
        $scenario = $this->getScenario();
        switch ($scenario) {
            case 'decrementCountOfOldTagsOnOldStores':
                $this->decrementCountOfOldTagsOnOldStores();
                break;
            case 'incrementCountOfSelectedTagsOnSelectedStores':
                $this->incrementCountOfSelectedTagsOnSelectedStores();
                break;
            case 'manageCountWhilePostAndVisibilityIsOn':
                $this->incrementAddedTagsOnSelectedStores();
                $this->incrementPreviousRemainedTagsOnNewStores();
                $this->decrementOldTagsOnDeletedStores();
                $this->decrementDeletedTagsOnOldStores();
                break;
            default: 
                break;
        }
        
        return $this;
    }
    
    /**
     * The tag count will be increased/decreased by the following rule:
     * 1. if the post status changed from PUBLISHED to something else -> the tags that were added to the relationtable will be decreased by one
     * 2. if the post status changed to PUBLISHED, the tags that were added - will be increased
     * 3. if the post status did not change and it is publish - there are 3 other scenarios inside
     * 4. if it is a new post and the post status is enabled from begining - the selected tags will be added on selected websites
     * 
     * @return string|boolean
     */
    protected function getScenario()
    {
        if (!is_null($this->_scenario))
        {
            return $this->_scenario;
        }
        
        if ($this->statusChangedFromPublish() && $this->visibilityHasEnabled())
        {
            return 'decrementCountOfOldTagsOnOldStores';
        }
        
        if ($this->statusChangedToPublish() && $this->visibilityIsEnabled())
        {
            return 'incrementCountOfSelectedTagsOnSelectedStores';
        }
        
        if ($this->statusHasNotChangedFromPublish())
        {
            if ($this->visibilityHasChangedFromEnabled())
            {
                return 'decrementCountOfOldTagsOnOldStores';
            }
            
            if ($this->visibilityHasChangedToEnabled())
            {
                return 'incrementCountOfSelectedTagsOnSelectedStores';
            }
            
            if ($this->visibilityHasNotChangedFromEnabled())
            {
                return 'manageCountWhilePostAndVisibilityIsOn';
            }
        }
        
        if (($this->statusSetToPublish() && $this->visibilitySetToEnabled()))
        {
            return 'incrementSelectedTagsOnSelectedStoresOnNewPost';
        }
        
        return false;
    }
    
    /**
     * Decrement old tags on deleted stores (if there are any)
     * If there are no removed websites or we are on a specific store - nothing will happen
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function decrementOldTagsOnDeletedStores()
    {
        $object = $this->getPost();
        $deletedWebsites = array_diff($object->getOrigData('website_ids'), $object->getWebsiteIds());
        if (!count($deletedWebsites) || $object->getStoreId())
        {
            return $this;
        }
        
        if (Mage::app()->isSingleStoreMode())
        {
            return $this->updateCountOnSingleStore($this->getOldIds(), $object->getStoreId());
        }
        
        $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($this->getOldIds(), $deletedWebsites, '-');
        $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn);

        return $this;
    }
    
    /**
     * Decrement deleted tags on old stores (if there are deleted tags and old stores)
     * The count update will happen only if there are old stores and removed tag
     * While on a specific store, in order to update the count, the post has to be visible on the store
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function decrementDeletedTagsOnOldStores()
    {
        $object = $this->getPost();
        $oldWebsites = $this->isOnDefaultStore() ? $object->getOrigData('website_ids') : $object->getWebsiteIds();
        if (!count($this->getDeletedIds()) || empty($oldWebsites))
        {
            return $this;
        }
        
        if ($this->canUpdateCountWhileOnSpecificStore($oldWebsites))
        {
            return $this->updateCountOnSingleStore($this->getDeletedIds(), $object->getStoreId());
        } 
        
        if ($this->isOnDefaultStore()) {
            $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($this->getDeletedIds(), $oldWebsites, '-');
            $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn);
        }
        return $this;
    }
    
    /**
     * Incrementing the added tags on selected stores
     * In order to update the count, there have to be new tags added to the post entity
     * and, if the action is triggered while on specific store, the post must have the store selected for visibility
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function incrementAddedTagsOnSelectedStores()
    {
        if (!count($this->getAddedIds()))
        {
            return $this;
        }
        
        $object = $this->getPost();
        if ($this->canUpdateCountWhileOnSpecificStore($object->getWebsiteIds()))
        {
            return $this->updateCountOnSingleStore($this->getAddedIds(), $object->getStoreId(), '+');
        }
        
        if ($this->isOnDefaultStore()) {
            $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($this->getAddedIds(), $object->getWebsiteIds(), '+');
            $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn,'+');
        }

        return $this;
    }
    
    /**
     * Prepares data to update count on new stores for the 3rd case
     * The tags will be incremented only if there is a new website selected
     */
    protected function incrementPreviousRemainedTagsOnNewStores()
    {        
        $object = $this->getPost();
        $differenceTags = array_diff($this->getSelectedIds(), $this->getAddedIds());
        $newWebsites = array_diff($object->getWebsiteIds(), $object->getOrigData('website_ids'));
        if (!count($newWebsites) || $object->getStoreId() || Mage::app()->isSingleStoreMode() || empty($differenceTags))
        {
            return $this;
        }
        
        $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($differenceTags, $newWebsites, '+');
        $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn,'+');

        return $this;
    }
    
    /**
     * Decrementing count of old tags on old stores for each tag individually
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function decrementCountOfOldTagsOnOldStores()
    {
        if (!count($this->getOldIds()))
        {
            return $this;
        }
        
        $object = $this->getPost();
        if ($this->canUpdateCountWhileOnSpecificStore($object->getOrigData('website_ids')))
        {
            return $this->updateCountOnSingleStore($this->getOldIds(), $object->getStoreId());
        }
        
        if ($this->isOnDefaultStore()) {
            $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($this->getOldIds(), $object->getOrigData('website_ids'), '-');
            $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn);
        }
        
        return $this;
    }
    
    /**
     * Incrementing count of selected tags on selected stores
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function incrementCountOfSelectedTagsOnSelectedStores()
    {
        if (!count($this->getSelectedIds()))
        {
            return $this;
        }
        
        $object = $this->getPost();
        if ($this->canUpdateCountWhileOnSpecificStore($object->getWebsiteIds()))
        {
            return $this->updateCountOnSingleStore($this->getSelectedIds(), $object->getStoreId(), '+');
        } 
        
        if ($this->isOnDefaultStore()) {
            $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForUpdateCountForArray($this->getSelectedIds(), $object->getWebsiteIds(), '+');
            $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn, '+');
        }
        
        return $this;
    }
    
    /**
     * Before performing an action on a specific store,
     * first we have to check if the specific post is displayed for the website
     * 
     * @param array $websiteIds websiteIds where the post`s visibility is checked
     * @return boolean
     */
    protected function canUpdateCountWhileOnSpecificStore(array $websiteIds)
    {
        $object = $this->getPost();
        $this->getStoresGroupedByWebsiteIdsArray();
        $stores = $this->getStoresByWebsiteId($websiteIds);
        
        return ($object->getStoreId() && in_array($object->getStoreId(), $stores)) 
            || Mage::app()->isSingleStoreMode();
    }
    
    /**
     * Check if the post is on default store
     * 
     * @return bool
     */
    protected function isOnDefaultStore()
    {
        return $this->getPost()->getStoreId() == 0 && !Mage::app()->isSingleStoreMode();
    }
    
    /**
     * Preparing stores data in order to be able to access stores and tags by each entity separately
     * 
     * @param bool $storesByWebsite sets the stores grouped by website array
     * @param bool $storesWithoutDefault sets the stores that don`t use the default values array
     * @param bool $storesByTag sets the stores grouped by tags array
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function _prepareStores($storesByWebsite = true, $storesWithoutDefault = true, $storesByTag = true)
    {
        if ($storesByWebsite) {
            $this->getStoresGroupedByWebsiteIdsArray();
        }
        
        if ($storesWithoutDefault) {
            $this->getStoresThatDontUseDefaultValues();
        }
        
        if ($storesByTag){
            $this->getStoresGroupedByTagIdsArray();
        }
        
        return $this;
    }
    
    /**
     * Creating an array of tag_id->stores to make changes on
     * In order to be used for decrementing the count
     * In this case - the stores where exist tag relations will be checked too
     * 
     * @param array $tagIds
     * @param array $websiteIds
     * @param string $operation
     * @return array
     */
    protected function createTagsAndStoresForUpdateCountForArray($tagIds, $websiteIds, $operation)
    {
        $this->_prepareStores();
        $storeIds = $this->getStoresByWebsiteId($websiteIds);
        
        $tagsAndStoresToChangeCountOn = array();
        foreach ($tagIds as $tagId)
        {            
            $tagsAndStoresToChangeCountOn[$tagId] = (array) $this->setStoresToChangeCountOn($tagId, $storeIds, $operation);
        }
        
        return $tagsAndStoresToChangeCountOn;
    }
    
    /**
     * Set stores to change count on by specific tagId
     * 
     * @param int $tagId
     * @param array $storeIds
     * @param string $operation
     * @return array
     */
    protected function setStoresToChangeCountOn($tagId, $storeIds, $operation)
    {
        $storesToChangeCountOn = array();
        if ($operation == '+')
        {
            $storesToChangeCountOn = (isset($this->_storesGroupedByTagIds[$tagId]))
                ? array_intersect($this->_storesGroupedByTagIds[$tagId], array_diff($this->_storesGroupedByTagIds[$tagId], $this->_storesThatDontUseDefaultValues))
                : array_diff($storeIds, $this->_storesThatDontUseDefaultValues);
        }
        
        if ($operation == '-')
        {
            $storesToChangeCountOn = array_intersect($this->_storesGroupedByTagIds[$tagId], array_diff($storeIds, $this->_storesThatDontUseDefaultValues));
        }
                
        if (!in_array(0, $storesToChangeCountOn)) {
            array_push($storesToChangeCountOn, 0);
        }

        return $storesToChangeCountOn;
    }
    
    /**
     * Updating tag count on multiple stores
     * 
     * @param array $tagsAndStoresToChangeCountOn 
     * @param string $operator +/-
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function updateCountOnMultipleStores(array $tagsAndStoresToChangeCountOn, $operator='-')
    {
        if (empty($tagsAndStoresToChangeCountOn)) {
            return $this;
        }

        try {
            Mage::getResourceModel('evozon_blog/tag')->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn, $operator);
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }
    
    /**
     * Updating tag count on single store
     * 
     * @param array $tagIds tag ids to apply the action to
     * @param int $storeId store id to apply the action to
     * @param string $operator +/-
     * @return \Evozon_Blog_Model_Post_Tag
     */
    protected function updateCountOnSingleStore(array $tagIds,  $storeId, $operator='-')
    {
        if (empty($tagIds)) {
            return $this;
        }
        
        try {
            Mage::getResourceModel('evozon_blog/tag')->updateCountOnSingleStore($tagIds,  $storeId, $operator);
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Called in the websites model in order to create an array
     * such as array([website_id]=>array(store_ids values),..);
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    public function getStoresGroupedByWebsiteIdsArray()
    {
        if (empty($this->_storesGroupedByWebsiteIds))
        {
            foreach (Mage::app()->getWebsites() as $website) {
                $this->_storesGroupedByWebsiteIds[$website->getId()] = $website->getStoreIds();
            }
        }

        return $this->_storesGroupedByWebsiteIds;
    }
    
    /**
     * Get tags related to current post and the stores the relation is applied on
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getStoresGroupedByTagIdsArray()
    {
        if (!is_array($this->_storesGroupedByTagIds)) {
            $tagsStoreSelect = $this->_getResource()->getTagsAndStoresByPostId($this->getPost()->getId());
            
            $storesByTagId = array();
            foreach ($tagsStoreSelect as $row => $data) {
                $storesByTagId[$data['tag_id']][] = $data['store_id'];
            }
            
            $this->_storesGroupedByTagIds = $storesByTagId;
        }
        
        return $this->_storesGroupedByTagIds;
    }
    
    /**
     * Finding the stores for the curent post on which the default values are not being used
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getStoresThatDontUseDefaultValues()
    {
        if (!is_array($this->_storesThatDontUseDefaultValues)) {
            $postStores = Mage::getResourceModel('evozon_blog/post')->getStoresStatusOnUsingDefaultValuesByPostId($this->getPost()->getId());
            $this->_storesThatDontUseDefaultValues = array_keys($postStores, 0);
        }
        
        return $this->_storesThatDontUseDefaultValues;
    }
    
    /**
     * Before deleting the post,
     * the tag count for the tags that are on active posts have to be decremented
     * 
     * @return \Evozon_Blog_Model_Post_Tag
     */
    public function updateTagCountOnDelete()
    {        
        $this->_prepareStores();
        if (!count($this->_storesGroupedByTagIds))
        {
            return $this;
        }
        
        $tagsAndStoresToChangeCountOn = $this->createTagsAndStoresForDecrementOnPostDeleteArray();
        if (!empty($tagsAndStoresToChangeCountOn)) {
            $this->updateCountOnMultipleStores($tagsAndStoresToChangeCountOn);
        }
        
        return $this;
        
    }
    
    /**
     * Finding the stores for the curent post
     * where the post is enabled and visible
     * in order to use them to decrement the count on post beforeDelete
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function createTagsAndStoresForDecrementOnPostDeleteArray()
    {
        $postStoresValidation = Mage::getResourceModel('evozon_blog/post')->getStoresWhereEntityIsEnabledAndVisibleByPostId($this->getPost()->getId());
        $activeStores = $this->getStoresByWebsiteId($this->getPost()->getOrigData('website_ids'));
        if ($postStoresValidation[0])
        {
            $invalidStores = array_keys($postStoresValidation, 0);
            $storesToChangeCount = array_diff($activeStores, $invalidStores);
            
            if (!empty($storesToChangeCount))
            {
                return $this->setTagsAndStoresToChangeCountOnPostDelete($storesToChangeCount);
            }
        }
        
        if (!$postStoresValidation[0])
        {
            $validStores = array_keys($postStoresValidation, 1);
            $storesToMatchPostWebsites = array_intersect($activeStores, $validStores);
            
            if (!empty($storesToMatchPostWebsites))
            {
                return $this->setTagsAndStoresToChangeCountOnPostDelete($validStores);
            }
        }
        
        return array();
    }
    
    /**
     * Setting tags and stores in order to be decremented
     * 
     * @param array $stores
     * @return array
     */
    protected function setTagsAndStoresToChangeCountOnPostDelete(array $stores)
    {
        $tagsAndStoresToChangeCount = array();
        foreach ($this->_storesGroupedByTagIds as $tagId=>$storeIds)
        {
            $tagsAndStoresToChangeCount[$tagId] = array_intersect($stores, $storeIds);
        }

        return $tagsAndStoresToChangeCount;
    }

    /**
     * Setting the store ids for selected websites
     * 
     * @param array $websiteIds
     * @return array
     */
    protected function getStoresByWebsiteId(array $websiteIds)
    {
        if (empty($websiteIds)) {
            return array();
        }

        $stores = array(0);
        foreach ($websiteIds as $id) {
            foreach ($this->_storesGroupedByWebsiteIds[$id] as $store) {
                $stores[] = $store;
            }
        }

        return $stores;
    }
    
    /***************************************************************************/
    /****     Conditionals check for post status and store visibility   ********/
    /***************************************************************************/

       /**
     * @return bool
     */
    protected function statusChangedFromPublish()
    {
        $object = $this->getPost();
        $published = Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED;
        $currentStatus = $object->getStatus();
        
        return $object->getOrigData('status') == $published && !empty($currentStatus) && $currentStatus != $published;
    }
    
    /**
     * @return bool
     */
    protected function statusChangedToPublish()
    {
        $object = $this->getPost();
        $published = Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED;
        $currentStatus = $object->getStatus();
        
        return !empty($currentStatus) && $currentStatus == $published  && $object->getOrigData('status') != $published;
    }
    
    /**
     * @return bool
     */
    protected function statusHasNotChangedFromPublish()
    {
        $object = $this->getPost();
        $published = Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED;
        $status = $object->getStatus();
        
        return ($status == $published || empty($status)) 
            && $object->getOrigData('status') == $published;
    }
    
    /**
     * @return bool
     */
    protected function statusSetToPublish()
    {
        $object = $this->getPost();
        $origStatus = $object->getOrigData('status');
        
        return is_null($origStatus) 
            && $object->getStatus() == Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED;
    }
    
    /**
     * Covers the cases for visibility being enabled-enabled or disabled-enabled
     * @return bool
     */
    protected function visibilityIsEnabled()
    {
        $object = $this->getPost();
        $enabled = Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
        $storeVisibility = $object->getStoreVisibility();
        $origStoreVisibility = $object->getOrigData('store_visibility');
        
        return ($origStoreVisibility == $enabled && empty($storeVisibility))
            || ($storeVisibility == $enabled && !empty($origStoreVisibility));
    }
    
    /**
     * Covers the cases for visibility being enabled-enabled or disabled-enabled or enabled-disabled
     * @return bool
     */
    protected function visibilityHasEnabled()
    {
        $object = $this->getPost();
        $enabled = Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
        $origStoreVisibility = $object->getOrigData('store_visibility');
        
        return ($origStoreVisibility == $enabled || $object->getStoreVisibility() == $enabled) && !empty($origStoreVisibility);
    }
    
    /**
     * @return bool
     */
    protected function visibilitySetToEnabled()
    {
        $object = $this->getPost();
        return is_null($object->getOrigData('store_visibility')) 
            && $object->getStoreVisibility() == Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
    }
    
    /**
     * @return bool
     */
    protected function visibilityHasNotChangedFromEnabled()
    {
        $object = $this->getPost();
        $enabled = Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
        $storeVisibility = $object->getStoreVisibility();
         
        return (empty($storeVisibility) || $storeVisibility == $enabled) && $object->getOrigData('store_visibility') == $enabled;
    }
    
    /**
     * @return bool
     */
    protected function visibilityHasChangedToEnabled()
    {
        $object = $this->getPost();
        
        return $object->getOrigData('store_visibility') == Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_DISABLED 
            && $object->getStoreVisibility() == Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
    }
    
    /**
     * @return bool
     */
    protected function visibilityHasChangedFromEnabled()
    {
        $object = $this->getPost();
        
        return $object->getStoreVisibility() == Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_DISABLED 
            && $object->getOrigData('store_visibility') == Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
    }
}
