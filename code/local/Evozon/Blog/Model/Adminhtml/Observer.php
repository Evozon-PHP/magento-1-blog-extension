<?php

/**
 * Contains the observers for our module
 *
 * @package    Evozon_Blog
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Adminhtml_Observer extends Evozon_Blog_Model_Abstract
{

    /**
     * This method will run when the product is saved
     * and it will deserialize the input from the custom added tab "Related Blog Posts"
     * and then it will save in the linkage table to be used further
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Varien_Event_Observer $observer
     * @return Evozon_Blog_Model_Adminhtml_Observer
     * @event catalog_product_save_after
     */
    public function saveProductPostRelation(Varien_Event_Observer $observer)
    {
        $productId = $observer->getProduct()->getId();
        $posts = Mage::app()->getRequest()->getPost('posts', -1);

        if ($posts != -1) {
            $post = Mage::helper('adminhtml/js')->decodeGridSerializedInput($posts);
            if (!is_array($post)) {
                $post = (array) $post;
            }

            Mage::getResourceSingleton('evozon_blog/catalog_product')
                ->updateProductPosts($productId, $post);
        }

        return $this;
    }

    /**
     * add 2 tabs into category edit form:
     *  1. list all blog articles that has been attached to category
     *  2. add additional settings for category layout
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param Varien_Event_Observer $observer
     * @return \Evozon_Blog_Model_Adminhtml_Observer
     * @event adminhtml_catalog_category_tabs
     */
    public function addCategoryBlogTabs(Varien_Event_Observer $observer)
    {
        $tabs = $observer->getEvent()->getTabs();
        $tabs->addTab(
            'evozon_blog_posts', array(
            'label' => Mage::helper('evozon_blog')->__('Blog Posts'),
            'title' => Mage::helper('evozon_blog')->__('Blog Posts'),
            'url' => Mage::getUrl('*/catalog_category/loadgrid', array('_current' => true)),
            'class' => 'ajax',
            )
        );

        return $this;
    }

    /**
     * Save the post - category relation on catalog category save after event.
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @param Varien_Event_Observer $observer
     * @return \Evozon_Blog_Model_Adminhtml_Observer
     * @event catalog_category_save_after
     */
    public function savePostCategoryRelation(Varien_Event_Observer $observer)
    {
        $categoryId = $observer->getCategory()->getId();
        $relations = Mage::app()->getRequest()->getPost('post_relations', '-1');

        if ($relations != '-1') {

            $relations = Mage::helper('adminhtml/js')->decodeGridSerializedInput($relations);
            if (!is_array($relations)) {
                $relations = (array) $relations;
            }

            Mage::getResourceSingleton('evozon_blog/catalog_category')
                ->updateCategoryPosts($categoryId, $relations);
        }

        return $this;
    }

    /**
     * Save correct date format in widget
     *
     * @author  Calin Florea <calin.florea@evozon.com>
     * @param   Varien_Event_Observer $observer
     * @return  \Evozon_Blog_Model_Adminhtml_Observer
     * @event   widget_widget_instance_save_before
     */
    public function widgetDateSaveBefore(Varien_Event_Observer $observer)
    {
        $allowedCharsArray = array('M', 'm', 'F', 'Y', 'y');
        $widgetInstance = $observer->getDataObject();
        $widgetParametersArray = $widgetInstance->getWidgetParameters();

        if ('evozon_blog/post_archive_filter' == $widgetInstance->getType() && !empty($widgetParametersArray['date_format'])) {

            $widgetParametersArray['date_format'] = Mage::helper('evozon_blog')
                ->keepOnlyAllowedChararacters($allowedCharsArray, $widgetParametersArray['date_format']);
            if (empty($widgetParametersArray['date_format'])) {
                throw new Exception('Unwanted characters has been removed Date format field and Date format cannot be empty');
            }

            $widgetInstance->setWidgetParameters(serialize($widgetParametersArray));
        }

        return $this;
    }

    /**
     * Verify if the spam checker key is valid 
     * Show a notification message if the key is not valid 
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @throws Exception
     * @return \Evozon_Blog_Model_Adminhtml_Observer
     */
    public function verifySpamCheckerKey()
    {        
        if (!(bool) $this->getConfigModel()->getCommentsSpamCheckerConfig(Evozon_Blog_Model_Config_Comment::SPAM_CHECKER_ENABLED)) {
            return $this;
        }
        
        $serviceFactory = Mage::getModel('evozon_blog/spam_factory');
        /** @var Evozon_Blog_Model_Spam_Service_Interface_IChecker $spamService */
        $spamService = $serviceFactory->getSpamService();

        try {
            if ($spamService->isEnabled() && !$spamService->isKeyValid()) 
            {
                $spamService->disableSpamChecker();
            }
        } catch (\Exception $ex) {
            Mage::logException($ex);
        }

        return $this;
    }
}
