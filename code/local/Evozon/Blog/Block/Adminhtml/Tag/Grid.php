<?php

/**
 * Grid block for tags
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Tag_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Initialize grid with identity and defaults
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for our grid
        $this->setDefaultSort('entity_id');
        $this->setId('evozon_blog_tag_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
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
     * Prepare the collection
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('evozon_blog/tag')
            ->getResourceCollection()
            ->addAttributeToSelect(array('entity_id', 'name', 'created_at','count'));

        $store = $this->_getStore();
        if ($store->getId()) {
            $collection->joinAttribute('store_tag', 'evozon_blog_tags/name', 'entity_id', null, 'inner', $store->getId());
        }

        $this->setCollection($collection);
        
        parent::_prepareCollection();
        $this->getCollection()->setProperDateFormat();
        
        return $this;
    }

    /**
     * Prepare grid's columns
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('entity_id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'entity_id'
        ));

        // Add tag name
        $this->addColumn('name', array(
            'header' => $this->__('Tag name'),
            'index' => 'name'
        ));

        if ($this->_getStore()->getId()) {
            $this->addColumn('store_tag', array(
                'header' => Mage::helper('evozon_blog')->__('Name in %s', $this->_getStore()->getName()),
                'align' => 'left',
                'index' => 'store_tag',
            ));
        }

        // Add post's created at
        $this->addColumn('created_at', array(
            'header' => $this->__('Created at'),
            'index' => 'created_at'
            )
        );

        // Add the posibility to take action for each row
        $this->addColumn('action_widget', array(
            'header' => Mage::helper('evozon_blog')->__('Action'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' =>
            array(
                array(
                    'caption' => Mage::helper('evozon_blog')->__('Edit'),
                    'url' => array('base' => '*/*/edit'),
                    'field' => 'id'
                ),
            ),
            'filter' => false,
            'sortable' => false
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * Mass actions for grid records (delete)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Grid
     */
    protected function _prepareMassaction()
    {
        // the field with checkboxes, on which mass actions will take place
        $this->setMassactionIdField('entity_id');

        // attribute name of the main selector which will be grabbed as post var in controller
        $this->getMassactionBlock()->setFormFieldName('tags');

        // add delete post action
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('evozon_blog')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('evozon_blog')->__('Are you sure you want to delete these tags?')
        ));

        return $this;
    }

    /**
     * Get the url for row edit
     * 
     * @return string Url for edit
     */
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
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
