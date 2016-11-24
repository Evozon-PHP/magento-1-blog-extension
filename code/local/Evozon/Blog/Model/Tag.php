<?php

/**
 * Tag model.
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Tag extends Mage_Catalog_Model_Abstract
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_tags';
    
    /**
     * Cache tag
     */
    const CACHE_TAG = 'evozon_blog_tags';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'evozon_blog_tags';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'tag';

    /**
     * Initialize resources
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('evozon_blog/tag');
    }

    /**
     * Retrieve store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }

        return Mage::app()->getStore()->getStoreId();
    }

    /**
     * Date and time has following format when submitted:
     * [publish_date] => 04/7/2015 
     * [publish_time] => Array ( [0] => 04 [1] => 04 [2] => 04 ) 
     * But it should be of YYYY-mm-dd H:m:s. Fix this issue before save.
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Tag
     */
    public function _beforeSave()
    {
        parent::_beforeSave();

        $inputData = $this->getInputData();
        $this->validate($inputData);

        $name = $inputData['default'];
        $this->setName($name)
            ->setAttributeSetId($this->getDefaultAttributeSetId())
            ->setUrlKey($this->formatUrlKey($name));

        if ($this->isObjectNew())
        {
            $this->addData(array('is_new'=>true));
        }

        return $this;
    }

    /**
     * Get collection instance
     *
     * @return object
     */
    public function getResourceCollection()
    {
        $collection = Mage::getResourceModel('evozon_blog/tag_collection');
        $collection->setStoreId($this->getStoreId());

        return $collection;
    }

    /**
     * Retrieve default attribute set id
     *
     * @return int
     */
    public function getDefaultAttributeSetId()
    {
        return $this->getResource()->getEntityType()->getDefaultAttributeSetId();
    }

    /**
     * Form Key for Url
     * This is key is not subject to being edited by the user
     * It is generated on object save
     * 
     * @param string $name
     * @return string
     */
    public function formatUrlKey($name)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($name));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    /**
     * Accesing a tag and getting all the values
     * for the existing stores
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getAllStoreValues()
    {
        return $this->getResource()->getAllAttributeValues($this);
    }

    /**
     * Validate the parameters from request (frontend)
     * Default value is required
     * Each tag added has to be unique on the specific store
     * Before allowing it to be saved, we have to check their uniqueness constraint
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $tagInput
     * @return array
     */
    protected function validate($tagInput)
    {
        $validator = $this->getValidator();
        $isValid = $validator->isValid($tagInput);

        if (!$isValid) {
            throw new Evozon_Blog_Model_Exception_Validate($validator->getMessages());
        }

        return $this;
    }

    /**
     * Getting a unique validator instance
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Tag_Validate_Unique
     */
    protected function getValidator()
    {
        return Mage::getModel('evozon_blog/tag_validate_unique', array('tag' => $this->getId()));
    }
    
    /**
     * Accessing validator config
     * 
     * @return Evozon_Blog_Model_Filter_Config
     */
    public function getValidatorConfig()
    {
        return Mage::getSingleton('evozon_blog/filter_config');
    }

}
