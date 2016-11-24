<?php

/**
 * Observer that will add events in fe and be that are related to catalog category
 * 
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Catalog_Observer
{

    /**
     * Observer adds a related articles block to the produt page
     * The handle`s name from blog.xml is also the name of our event handling in config.xml
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Catalog_Observer
     * @event  controller_action_layout_load_before
     */
    public function insertBlock($observer)
    {
        $controller = $observer->getAction();

        if ($controller->getFullActionName() != 'catalog_product_view') {
            return;
        }

        $layout = $controller->getLayout()->getUpdate();
        $layout->addHandle('evozon_blog_related_posts_on_product_page');

        return $this;
    }

    /**
     * Observer that will lock custom added attributes on the Category Custom Design tab
     * It is required to lock our added attributes so that the user won`t be able to select them
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Catalog_Observer 
     * @event adminhtml_catalog_category_edit_prepare_form
     */
    public function lockCustomAddedAttributes($observer)
    {
        $form = $observer->getEvent()->getForm();
        $categoryData = $form->getDataObject();

        if ($categoryData->getData('custom_use_parent_settings')) {
            $categoryData->lockAttribute('is_blog_category');
            $categoryData->lockAttribute('show_featured_posts');
        }

        return $this;
    }

}
