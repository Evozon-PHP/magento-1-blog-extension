<?php

/**
 * Define grid content: grid identity, columns to show, collection of items
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Initialize grid with identity and defaults
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setId('postsGrid');

        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
        $this->setVarNameSort('sort');
    }

    /**
     * Get the store selected by user.
     *
     * @return Mage_Core_Model_Store
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * Prepare the collection and set it as member variable
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('evozon_blog/post')->getCollection()
            ->addAttributeToSelect(array('title', 'url_key', 'status', 'publish_date', 'store_visibility'));
        
        $collection->joinAttribute('default_status', 'evozon_blog_post/status', 'entity_id', null, 'left', Mage_Core_Model_App::ADMIN_STORE_ID);

        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->joinAttribute('evozon_blog_post_title', 'evozon_blog_post/title', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('evozon_blog_post_status', 'evozon_blog_post/status', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('evozon_blog_post_visibility', 'evozon_blog_post/store_visibility', 'entity_id', null, 'inner', $store->getId());
            $collection->joinAttribute('evozon_blog_post_publish_date', 'evozon_blog_post/publish_date', 'entity_id', null, 'inner', $store->getId());
        }
        
        $this->setCollection($collection);

        parent::_prepareCollection();
        $this->getCollection()->addWebsiteNamesToCollection();
        $this->getCollection()->setProperDateFormat();
        
        return $this;
    }

    /**
     *
     * @param type $column
     * @return type
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField('websites', 'evozon_blog/post_website', 'website_id', 'post_id=entity_id', null, 'left');
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Prepare grid's columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _prepareColumns()
    {
        // add the columns that should appear in the grid
        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'entity_id',
            'type' => 'number'
            )
        );

        // add post title
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'index' => 'title',
            )
        );

        if ($this->_getStore()->getId()) {
            $this->addColumn('evozon_blog_post_title', array(
                'header' => Mage::helper('evozon_blog')->__('Title in %s', $this->_getStore()->getName()),
                'align' => 'left',
                'index' => 'evozon_blog_post_title',
            ));

            // add post's STORE visibility
            $this->addColumn('evozon_blog_post_visibility', array(
                'header' => $this->__('Store Visibility'),
                'width' => '70px',
                'index' => 'evozon_blog_post_visibility',
                'type' => 'options',
                'options' => Mage::getModel('catalog/product_status')->getOptionArray(),
                )
            );
        }

        // Add url key of the post
        $this->addColumn('url_key', array(
            'header' => $this->__('Url key'),
            'index' => 'url_key'
            )
        );

        if ($this->_getStore()->getId()) {
            // add post's STORE status
            $this->addColumn('evozon_blog_post_status', array(
                'header' => $this->__('Status on store'),
                'index' => 'evozon_blog_post_status',
                'type' => 'options',
                'options' => Mage::getSingleton('evozon_blog/adminhtml_post_status')->getAllOptions(),
                )
            );

            // add post`s STORE publish date
            $this->addColumn('evozon_blog_post_publish_date', array(
                'header' => $this->__('Publishing date'),
                'index' => 'evozon_blog_post_publish_date'
            ));
        } else {
            // add post's STORE visibility
            $this->addColumn('store_visibility', array(
                'header' => $this->__('Visibility (default)'),
                'width' => '70px',
                'index' => 'store_visibility',
                'type' => 'options',
                'options' => Mage::getModel('catalog/product_status')->getOptionArray(),
                )
            );

            // add post's default status
            $this->addColumn('default_status', array(
                'header' => $this->__('Status'),
                    'index' => 'default_status',
                'type' => 'options',
                'options' => Mage::getSingleton('evozon_blog/adminhtml_post_status')->getAllOptions(),
                )
            );

            // add post's created at
            $this->addColumn('created_at', array(
                'header' => $this->__('Created at'),
                'index' => 'created_at'
                )
            );

            // add post's publish date
            $this->addColumn('publish_date', array(
                'header' => $this->__('Publishing date'),
                'index' => 'publish_date'
                )
            );

            if (!Mage::app()->isSingleStoreMode()) {
                $this->addColumn('websites', array(
                    'header' => Mage::helper('evozon_blog')->__('Websites'),
                    'width' => '100px',
                    'sortable' => false,
                    'index' => 'websites',
                    'type' => 'options',
                    'options' => Mage::getModel('core/website')->getCollection()->toOptionHash(),
                ));
            }
        }

        $this->addColumn('action_widget', array(
            'header' => Mage::helper('evozon_blog')->__('Action'),
            'width' => '50px',
            'type' => 'action',
            'getter' => 'getId',
            'actions' =>
            array(
                array(
                    'caption' => Mage::helper('evozon_blog')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Mass actions for grid records
     * Delete, change status and change visiblity
     *
     * @return \Evozon_Blog_Block_Adminhtml_Post_Grid
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');

        $this->getMassactionBlock()->setFormFieldName('posts');

        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('evozon_blog')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('evozon_blog')->__('Are you sure you want to delete these posts?')
        ));

        $statuses = Mage::getSingleton('evozon_blog/adminhtml_post_status')->getMassActionOptions();

        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('evozon_blog')->__('Change Status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('evozon_blog')->__('Post Status'),
                    'values' => $statuses
                )
            )
        ));

        $this->getMassactionBlock()->addItem('visibility', array(
            'label' => Mage::helper('evozon_blog')->__('Change Store Visibility'),
            'url' => $this->getUrl('*/*/massVisibility', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'visibility',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('evozon_blog')->__('Post Store Visibility'),
                    'values' => Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray()
                )
            )
        ));

        return $this;
    }
    
    /**
     * Get the url for row edit
     *
     * @param Evozon_Blog_Model_Post $row
     * @return String Url for edit
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
                'store' => $this->getRequest()->getParam('store'),
                'id' => $row->getId())
        );
    }

    /**
     * Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
