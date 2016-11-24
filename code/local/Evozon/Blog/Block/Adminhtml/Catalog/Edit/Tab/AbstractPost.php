<?php

/**
 * Catalog implementations of posts grid will extend this abstract.
 * Main methods are the same. Others have to be implemented as are defined as abstract.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Block_Adminhtml_Catalog_Edit_Tab_AbstractPost extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Setting the grid parameters
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('post_catalog_posts_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

        //having the selected products "checked" and displayed
        if ($this->_getEntityId()) {
            $this->setDefaultFilter(array('in_posts' => 1));
        }
    }

    /**
     * Returning the store_id for filtering the displayed blog posts
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _getStoreId()
    {
        return (int) $this->getRequest()->getParam('store', 0);
    }

    /**
     * Prepare the collection of blog posts
     * Filtering according to the current product`s store_id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @access protected 
     * @return Evozon_Blog_Block_Adminhtml_Catalog_Product_Edit_Tab_Post
     * 
     */
    protected function _prepareCollection()
    {
        $storeId = $this->_getStoreId();

        //get blog posts
        $collection = Mage::getResourceModel('evozon_blog/post_collection')
            ->addAttributeToSelect(array('title', 'status', 'store_visibility', 'publish_date'));
        
        // get default status
        $collection->joinAttribute('default_status', 'evozon_blog_post/status', 'entity_id', null, 'left', Mage_Core_Model_App::ADMIN_STORE_ID);

        if ($storeId) {
            $collection->joinAttribute('evozon_blog_post_title', 'evozon_blog_post/title', 'entity_id', null, 'inner', $storeId);
            $collection->joinAttribute('evozon_blog_post_status', 'evozon_blog_post/status', 'entity_id', null, 'inner', $storeId);
            $collection->joinAttribute('evozon_blog_post_visibility', 'evozon_blog_post/store_visibility', 'entity_id', null, 'inner', $storeId);
            $collection->joinAttribute('evozon_blog_post_publish_date', 'evozon_blog_post/publish_date', 'entity_id', null, 'inner', $storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Add filter
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @access protected
     * @param object $column
     * @return Evozon_Blog_Block_Adminhtml_Catalog_Product_Edit_Tab_Post
     * 
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_posts') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $postIds));
            } else {
                if ($postIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $postIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }

    /**
     * Prepare the grid columns
     * We`re showing the articles according to required store_id and product_id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @access protected
     * @return Evozon_Blog_Block_Adminhtml_Catalog_Product_Edit_Tab_Post
     * 
     */
    protected function _prepareColumns()
    {
        //adding the checkbox column where we`ll check/uncheck the related blog posts
        $this->addColumn('in_posts', array(
            'header' => Mage::helper('evozon_blog')->__('Related blog posts'),
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'index' => 'entity_id',
            'align' => 'center',
            'field_name' => 'in_posts',
            'values' => $this->_getSelectedPosts(),
        ));

        if ($this->_getStoreId()) {
            $this->addColumn('evozon_blog_post_title', array(
                'header' => Mage::helper('evozon_blog')->__('Title'),
                'align' => 'left',
                'index' => 'evozon_blog_post_title',
            ));

            $this->addColumn('evozon_blog_post_visibility', array(
                'header' => Mage::helper('evozon_blog')->__('Visible on store'),
                'index' => 'evozon_blog_post_visibility',
                'type' => 'options',
                'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                )
            );

            //add blog post status
            $this->addColumn('evozon_blog_post_status', array(
                'index' => 'evozon_blog_post_status',
                'header' => Mage::helper('evozon_blog')->__('Status'),
                'type' => 'options',
                'options' => Mage::getSingleton('evozon_blog/adminhtml_post_status')->getAllOptions(),
            ));

            $this->addColumn('evozon_blog_post_publish_date', array(
                'header' => Mage::helper('evozon_blog')->__('Publish date'),
                'index' => 'evozon_blog_post_publish_date'
                )
            );
        } else {
            $this->addColumn('title', array(
                'header' => Mage::helper('evozon_blog')->__('Title'),
                'align' => 'left',
                'index' => 'title',
            ));

            $this->addColumn('store_visibility', array(
                'header' => Mage::helper('evozon_blog')->__('Store visibility (default)'),
                'index' => 'store_visibility',
                'type' => 'options',
                'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray(),
                )
            );
            
            //add blog post status
            $this->addColumn('default_status', array(
                'index' => 'default_status',
                'header' => Mage::helper('evozon_blog')->__('Status'),
                'type' => 'options',
                'options' => Mage::getSingleton('evozon_blog/adminhtml_post_status')->getAllOptions(),
            ));

            $this->addColumn('publish_date', array(
                'header' => Mage::helper('evozon_blog')->__('Publish date'),
                'index' => 'publish_date'
                )
            );
        }

        parent::_prepareColumns();
    }

    /**
     * Get row url
     * When selecting a row, we will be redirected to the edit page of the blog post
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @access public
     * @return string
     * 
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * Get the current product id
     * to be used for retrieving data and saving the relationship
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @access public
     * @return int (Mage_Catalog_Model_Product id)
     */
    protected function _getEntityId()
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * abstract methods: needs to be implemented in child class.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    abstract public function getSelectedPosts();

    abstract protected function _getSelectedPosts();
}
