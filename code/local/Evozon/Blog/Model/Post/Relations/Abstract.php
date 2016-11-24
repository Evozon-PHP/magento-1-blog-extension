<?php

/**
 * Abstract class for post-additional elements relations
 * Additional elements, at the release time are:
 * 1. websites
 * 2. categories
 * 3. products
 * 4. tags
 * 5. other posts
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Post_Relations_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * used Evozon_Blog_Model_Post object to get data
     */
    protected $_post;
    
    /**
     * Setting the model and the calling object 
     * 
     * @param Evozon_Blog_Model_Post $object
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init($this->getModelName());
    }
    
    /**
     * @return string  model name
     */
    abstract protected function getModelName();
    
    /**
     * @return string the key under which the data for the selected ids can be found
     */
    abstract protected function getSelectedIdsDataName();
    
    /**
     * getting collection of additional objects
     */
    abstract function getCollectionByPostId($postId);

    /**
     * Setting object used to get data from
     * 
     * @param Evozon_Blog_Model_Post $object
     * @return \Evozon_Blog_Model_Post_Abstract
     */
    public function setPost(Evozon_Blog_Model_Post $object)
    {
        $this->_post = $object;
        return $this;
    }
    
    /**
     * Object used to get data from
     * @return Evozon_Blog_Model_Post
     */
    public function getPost()
    {
        return $this->_post;
    }
    
    /**
     * Saving the entity relations function caller
     */
    public function saveRelations()
    {
        $this->_extractDataFromPost();
        
        $this->_getResource()
            ->setPostId($this->getPost()->getId())
            ->_addRelations($this->getAddedIds())
            ->_removeRelations($this->getDeletedIds());
        
        return $this;
        
    }
        
    /**
     * Setting data required in order to saveRelations() and other functions
     * setOldIds - ids from beforeSave
     * setAddedIds - new ids/relations added to the post object
     * setDeletedIds - ids that have been removed from relations to post
     * setSelectedIds - ids afterSave
     *
     * @return \Evozon_Blog_Model_Post_Abstract
     */
    protected function _extractDataFromPost()
    {
        $this->setOldIds($this->getOldIds());
        $this->setSelectedIds($this->getSelectedIds());
        
        $insert = array_diff($this->getSelectedIds(), $this->getOldIds());
        $delete = array_diff($this->getOldIds(), $this->getSelectedIds());
        
        $this->setAddedIds($insert);
        $this->setDeletedIds($delete);
        
        return $this;
    }
    
    /**
     * Getting selected ids by processing the post object
     * @return array
     */
    public function getSelectedIds()
    {
        if (!$this->hasData('selected_ids'))
        {
            $data = $this->getPost()->getData($this->getSelectedIdsDataName());
            $this->setData('selected_ids', $data);
        }

        return (array) $this->getData('selected_ids');
    }
    
    /**
     * Getting old ids
     * @return array
     */
    public function getOldIds()
    {
        if ($this->hasData('old_ids'))
        {
            $oldIds = $this->getIdsByPostId($this->getPost()->getId());
            $this->setData('old_ids', $oldIds);
        }
        
        return $this->getData('old_ids');
    }
    
    
    /**
     * Returning all ids that have relations to the calling object 
     * (ex: to the received Evozon_Blog_Model_Post $object)
     * 
     * @param int $postId
     * @param int $storeId
     * @return array
     */
    public function getIdsByPostId($postId, $storeId = 0)
    {
        return $this->_getResource()->getIdsByPostId($postId, $storeId);
    }
}
