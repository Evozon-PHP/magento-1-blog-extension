<?php
/**
 * Blog Post Stores tab
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Websites extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Websites
{
    protected $_storeFromHtml;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('evozon/blog/post/edit/tab/websites.phtml');
    }

    /**
     * Retrieve edited post model instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * Get store ID of current post
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getPost()->getStoreId();
    }

    /**
     * Get ID of current post
     *
     * @return int
     */
    public function getPostId()
    {
        return $this->getPost()->getId();
    }

    /**
     * Retrieve array of website IDs of current post
     *
     * @return array
     */
    public function getWebsites()
    {
        return $this->getPost()->getWebsiteIds();
    }

    /**
     * Returns whether post associated with website with $websiteId
     *
     * @param int $websiteId
     * @return bool
     */
    public function hasWebsite($websiteId)
    {
        return in_array($websiteId, $this->getPost()->getWebsiteIds());
    }

    /**
     * Check websites block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return $this->getPost()->getWebsitesReadonly();
    }

    /**
     * Retrieve store name by its ID
     *
     * @param int $storeId
     * @return null|string
     */
    public function getStoreName($storeId)
    {
        return Mage::app()->getStore($storeId)->getName();
    }
}
