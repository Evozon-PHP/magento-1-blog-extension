<?php

/**
 * Customer accounts comments toolbar
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Customer_Account_Comment_Toolbar extends Evozon_Blog_Block_Abstract
{

    /**
     * Comments collection
     * 
     * @var Evozon_Blog_Model_Resource_Comment_Collection
     */
    protected $_collection = null;

    /**
     * Page var name
     * @var string
     */
    protected $_pageVarName = null;

    /**
     * Status id to filter the comments collection
     *
     * @var int
     */
    protected $_currentFilter;

    /**
     * The field it will be filtered the collection by default
     *
     * @var int
     */
    protected $_filterField;

    /**
     * Setting defaults for constructing the toolbar
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_filterField = -1;

        $this->setTemplate("evozon/blog/customer/account/comments/toolbar.phtml");
    }

    /**
     * Set collection to pager
     *
     * @param Varien_Data_Collection $collection
     * @return Evozon_Blog_Block_Comment_Customer_Account_List_Toolbar
     */
    public function setCollection($collection)
    {
        $this->_collection = $collection;

        if ($this->getCurrentFilter() != -1) {
            $this->_collection->addFieldToFilter('main_table.status', $this->_currentFilter);
        }

        $this->_collection->setCurPage($this->getCurrentPage());
        if ($this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }

        return $this;
    }

    /**
     * Return products collection instance
     *
     * @return \Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Retrieve filter field GET var name
     *
     * @return string
     */
    public function getFilterVarName()
    {
        return $this->getConfigData(Evozon_Blog_Model_Config_General::TOOLBAR_AND_PAGINATION_FILTER_NAME);
    }

    /**
     * Getter for $_pageVarName
     *
     * @return string
     */
    public function getPageVarName()
    {
        if (is_null($this->_pageVarName)){
            $this->_pageVarName = $this->getConfigData(Evozon_Blog_Model_Config_General::TOOLBAR_AND_PAGINATION_PAGE_NAME);
        }

        return $this->_pageVarName;
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam($this->getPageVarName())) {
            return $page;
        }

        return 1;
    }

    /**
     * Get grid comments filter field
     * It will also store the filter id to know to filter the comments by the status
     *
     * If there is already selected a filter, we`ll return it
     * If it is the first time we access the page, we have to take the default filter
     * If we changed the filter, we have to check if the id is the same as the one selected
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int
     */
    public function getCurrentFilter()
    {
        $filter = $this->_getData('current_filter');
        if ($filter) {
            $this->_currentFilter = $filter;
            return $this->_currentFilter;
        }

        $filters = $this->getAvailableFilters();
        $defaultFilter = $this->_filterField;

        $this->setData('current_filter', $defaultFilter);


        /** @var string $filter */
        $filter = $this->getRequest()->getParam($this->getFilterVarName());
        $filterId = $this->getFilterId($filter);
        if ($filter && isset($filters[$filterId])) {
            if ($filterId !== $defaultFilter) {
                $this->setData('current_filter', $filterId);
            }
        }
        $this->_currentFilter = $this->getData('current_filter');
        return $this->_currentFilter;
    }

    /**
     * Compare defined filter field vith current filter field
     *
     * @param int $filter
     * @return bool
     */
    protected function isFilterCurrent($filter)
    {
        return ($filter == $this->_currentFilter);
    }

    /**
     * Retrieve Pager URL
     * It will contain data on filter, the ones that are setted
     *
     * @param string $filter
     * @return string
     */
    public function getSortingUrl($filter)
    {
        if (is_null($filter)) {
            $filter = $this->getAvailableFilters();
            $filter = $filter[$this->_currentFilter];
        }

        return $this->getPagerUrl(
            array(
                $this->getFilterVarName() => $filter,
                $this->getPageVarName() => null
            ));
    }

    /**
     * Return current URL with rewrites and additional parameters
     *
     * @param array $params Query parameters
     * @return string
     */
    public function getPagerUrl($params = array())
    {
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        
        return $this->getUrl('*/*/*', $urlParams);
    }

    /**
     * Finding in the available filters array the id of the status by the value
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $filter
     * @return int
     */
    protected function getFilterId($filter)
    {
        return array_search(ucfirst($filter), $this->getAvailableFilters());
    }

    /**
     * Accessing parameters from system configurations
     * 
     * @param string $key
     * @return int|string
     */
    protected function getConfigData($key = null)
    {
        return $this->getConfigModel()->getToolbarAndPaginationConfig($key);
    }

    /**
     * 
     * @return int
     */
    public function getFirstRangeCommentsNumber()
    {
        $collection = $this->getCollection();
        return (int) $collection->getPageSize() * ($collection->getCurPage() - 1) + 1;
    }

    /**
     * 
     * @return int
     */
    public function getSecondRangeCommentsNumber()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize() * ($collection->getCurPage() - 1) + $collection->count();
    }

    /**
     * 
     * @return int
     */
    public function getTotalCommentsNumber()
    {
        return $this->getCollection()->getSize();
    }

    /**
     * 
     * @return boolean
     */
    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    /**
     * 
     * @return int
     */
    public function getLastPageNumber()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChild('evozon_blog_comment_pager');

        if ($pagerBlock instanceof Varien_Object) {
            /* @var $pagerBlock Mage_Page_Block_Html_Pager */
            $pagerBlock->setUseContainer(false)
                ->setShowPerPage(false)
                ->setShowAmounts(false)
                ->setPageVarName($this->getPageVarName())
                ->setLimit($this->getLimit())
                ->setCollection($this->getCollection());

            return $pagerBlock->toHtml();
        }

        return '';
    }
}
