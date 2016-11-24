<?php

/**
 * Renderer for setting/editing tag values for all store views
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Tag_Edit_Tab_Options extends Mage_Adminhtml_Block_Template
{

    public function __construct()
    {
        $this->setTemplate($this->getTemplate());
        parent::__construct();
    }

    /**
     * Setting the template to render the field
     * 
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/tag/edit/options.phtml';
    }

    /**
     * Retrieve stores collection with default store
     *
     * @return Mage_Core_Model_Mysql4_Store_Collection
     */
    public function getStores()
    {
        $stores = $this->getData('stores');
        if (is_null($stores)) {
            $stores = Mage::getModel('core/store')
                ->getResourceCollection()
                ->setLoadDefault(true)
                ->load();
            $this->setData('stores', $stores);
        }

        return $stores;
    }

    /**
     * Retrieve frontend labels of attribute for each store
     * If there is edited data on the model, we will set that data instead
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getTagValues()
    {
        $object = $this->getTagObject();
        $tag = $object->getAllStoreValues();
        $editData = $object->getEdit();

        $values = array();
        foreach ($this->getStores() as $store) {
            $values[$store->getId()]['name'] = isset($tag[$store->getId()]['name']) ?
                $tag[$store->getId()]['name'] : '';
            if (isset($editData[$store->getId()])) {
                $values[$store->getId()]['name'] = $editData[$store->getId()];
            }

            $values[$store->getId()]['count'] = isset($tag[$store->getId()]['count']) ?
                $tag[$store->getId()]['count'] : '0';
        }

        return $values;
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function getTagObject()
    {
        return Mage::registry('evozon_blog_tag');
    }

    /**
     * If the template is used from the tag add or tag edit action,
     * The Magento required validation script will be added
     * 
     * @return boolean
     */
    public function addValidate()
    {
        if ($this->getUseValidate()) {
            return true;
        }

        return false;
    }

}
