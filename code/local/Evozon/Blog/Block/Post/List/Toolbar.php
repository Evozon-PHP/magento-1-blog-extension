<?php

/**
 * Create toolbar with next&previous post navigation.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_List_Toolbar extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * Total number of posts.
     *
     * @var int 
     */
    protected $_postCount = null;

    /**
     * The page parameter name
     * @var string
     */
    protected $_pageVarName;

    /**
     * Construct
     */
    protected function _construct()
    {
        parent::_construct();

        if (is_null($this->_pageVarName)) {
            $this->_pageVarName = Mage::getSingleton('evozon_blog/config')
                ->getGeneralConfig(Evozon_Blog_Model_Config_General::TOOLBAR_AND_PAGINATION_PAGE_NAME);
        }
    }

    /**
     * Get the collection overall count.
     * 
     * @author Iunia Bujita <iunia.bujita@evozon.com>
     * @return int
     */
    public function getPostCount()
    {
        if ($this->_postCount === null) {
            $postSelect = $this->getPostCollection();
            $this->_postCount = $postSelect->getSize();
        }

        return $this->_postCount;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int) $this->getRequest()->getParam($this->_pageVarName);
        if ($page) {
            return $page;
        }

        return 1;
    }

    /**
     * Given current page, return the url to previous page.
     * Does the logic of previous url:  >0 -> previous page, otherwise false
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return bool | string
     */
    public function getNewestUrl()
    {
        $prevPage = $this->getCurrentPage() - 1;
        $url = false;

        if ($prevPage > 0) {
            $url = Mage::getUrl($this->_getUrlPath(),
                    array(
                    '_use_rewrite' => true,
                        '_query' => array($this->_pageVarName => $prevPage)
                    )
            );
        }

        return $url;
    }

    /**
     * Given current page, return the url to next page or false if no url.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return bool | string
     */
    public function getOldestUrl()
    {
        $url = false;
        $postCount = $this->getPostCount();
        $limit = (int) $this->getConfigModel()->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_NUMBER_OF_POSTS);

        if ($postCount < 1 || $limit == 0 || $postCount <= $limit) {
            return $url;
        }

        $currentPage = $this->getCurrentPage();
        $totalPages = ceil($postCount / $limit);

        if ($currentPage != $totalPages) {
            $url = Mage::getUrl($this->_getUrlPath(),
                    array(
                    '_use_rewrite' => true,
                        '_query' => array($this->_pageVarName => $currentPage + 1)
                    )
            );
        }

        return $url;
    }

    /**
     * Return either the default, which is current frontname/controller/action,
     * or pathinfo in case or other list types like 'archive','tags','search'
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function _getUrlPath()
    {
        return Mage::getSingleton('evozon_blog/url')->getUrl($this->getListType());
    }

}
