<?php

/**
 * Frontend observer
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Observer extends Evozon_Blog_Model_Abstract
{

    /**
     * Contains original page handles
     * 
     * @var array
     */
    protected $_handles = array();

    /**
     * before load layout, check if blog layout should be loaded.
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @event controller_action_layout_load_before
     * @param Varien_Event_Observer $observer
     * @return \Varien_Event_Observer
     */
    public function onControllerActionLayoutLoadBefore(Varien_Event_Observer $observer)
    {
        // get the action name
        $fullActionName = $observer->getEvent()->getAction()->getFullActionName();

        // check if the category has been set, if we are in category controller
        if (
            $fullActionName == 'catalog_category_view' &&
            ($category = Mage::registry('current_category')) &&
            $category instanceof Mage_Catalog_Model_Category
        ) {
            // initialize is blog flag to current category value
            $isBlogCategoryFlag = $category->getIsBlogCategory();
            $pageLayout = $category->getPageLayout();

            // check if category has 'Use Parent Category Settings' 
            // and load the first parent with settings
            if ($category->getCustomUseParentSettings()) {
                $parentCategory = Mage::getResourceModel('evozon_blog/catalog_category')->getParentDesignCategory($category);
                $isBlogCategoryFlag = $parentCategory->getIsBlogCategory();
                $category->setIsBlogCategory($isBlogCategoryFlag);
                $pageLayout = $parentCategory->getPageLayout();
            }

            // get layout update object from observer
            /* @var $layoutUpdate Mage_Core_Model_Layout_Update */
            $layoutUpdate = $observer->getEvent()->getLayout()->getUpdate();

            // if category or parent category is of type blog, change category layout to load blog layout.
            if ($isBlogCategoryFlag) {

                // remove default category handles                
                $this->removeHandlesFromLayout($layoutUpdate->getHandles());
                $layoutUpdate->resetHandles();
                $this->addNeededHandles($layoutUpdate);

                // remove compare product and recently viewd products
                $layoutUpdate->addHandle('evozon_blog_removable_blocks');

                // add default layout handle
                $defaultLayout = $this->getConfigModel()->getGeneralConfig(Evozon_Blog_Model_Config_General::LAYOUT_DEFAULT);
                $layoutUpdate->addHandle('evozon_blog_category_view_one_column');

                // add default layout if is not one_column which is already added.
                if ($defaultLayout != 'one_column') {
                    $layoutUpdate->addHandle('evozon_blog_' . $defaultLayout);
                    $category->setBlogLayout($defaultLayout);
                }

                // add category specific page layout handle, if set.
                if (!empty($pageLayout) && $pageLayout != 'empty' && $pageLayout != 'one_column') {
                    if ($defaultLayout != 'one_column') {
                        $layoutUpdate->removeHandle('evozon_blog_' . $defaultLayout);
                    }
                    $layoutUpdate->addHandle('evozon_blog_' . $pageLayout);
                    $category->setBlogLayout($pageLayout);
                }
            } else {
                if ($category->getShowFeaturedPosts()) {

                    //if the user has used the custom layout update attribute to set the position of the block
                    $customLayout = $category->getCustomLayoutUpdate();
                    if (isset($customLayout) && strpos($customLayout, 'evozon_blog/catalog_category_posts')) {
                        return $this;
                    }

                    $layoutUpdate->addHandle('evozon_blog_catalog_category_featured_center');
                }
            }
        }
        
        return $this;
    }

    /**
     * Removing unneded handles from the layout 
     * So we can add required evozon blog category style layout handles
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $handles
     * @return \Evozon_Blog_Model_Observer
     */
    protected function removeHandlesFromLayout($handles)
    {
        foreach ($handles as $key => $handle) {
            if (strpos($handle, 'catalog_') === 0 || strpos($handle, 'CATEGORY_') === 0) {
                unset($handles[$key]);
            }
        }

        $this->_handles = $handles;
        return $this;
    }

    /**
     * After reseting the layout handles, the default layouts are added back
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param array $layoutUpdate
     * @return \Evozon_Blog_Model_Observer
     */
    protected function addNeededHandles($layoutUpdate)
    {
        foreach ($this->_handles as $handle) {
            $layoutUpdate->addHandle($handle);
        }

        return $this;
    }

}
