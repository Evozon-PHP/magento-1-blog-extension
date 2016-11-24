<?php

/**
 * Create the tab that will appear in the Admin of the Catalog->Products->Blog Posts
 *
 * @package    Evozon_Blog
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Catalog_Product_Tab extends Mage_Adminhtml_Block_Template
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
     /**
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function _construct()
    {
        parent::_construct();
    }

    /**
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        return $this;
    }

    /**
     * Retrieve the label used for the tab relating to this block
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('evozon_blog')->__('Blog Posts');
    }

    /**
     * Retrieve the title used by this tab
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('evozon_blog')->__('Click here to view the related blog posts');
    }

    /**
     * Stops the tab being hidden
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Retrieve the class name of the tab
     * Returns 'ajax'because weant the tab to be loaded via Ajax
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Determine whether to generate content on load or via AJAX
     * It is true, so that the tab's content won't be loaded until the tab is clicked
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function getSkipGenerateContent()
    {
        return true;
    }

    /**
     * Determines whether to display the tab
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function canShowTab()
    {
        if (Mage::registry('current_product')->getId()) {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the URL used to load the tab content
     *
     * @author     Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/catalog_product/posts', array('_current' => true));
    }

}
