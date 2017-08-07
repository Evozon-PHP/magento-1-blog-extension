<?php

/**
 * Define grid content: grid identity, columns to show, collection of items
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * initialize grid with identity and defaults
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();

        // Set some defaults for grid
        $this->setDefaultSort('id');
        $this->setId('evozon_blog_comment_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get the collection to be loaded: comment collection.     
     * 
     * @return String
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _getCollectionClass()
    {
        // This is the model collection used for the grid
        return 'evozon_blog/comment_collection';
    }

    /**
     * Prepare the collection and set it as member variable 
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = Mage::getResourceModel($this->_getCollectionClass());
            $collection->addPostDetails()
                ->addCustomerAndAdminJoin();
            
            $this->setCollection($collection);
        }
        
        parent::_prepareCollection();
        $this->getCollection()->setProperDateFormat();
        
        return $this;
    }

    /**
     * Prepare grid's columns
     * 
     * @return Mage_Adminhtml_Block_Widget_Grid
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id', array(
            'header' => $this->__('ID'),
            'align' => 'right',
            'width' => '50px',
            'index' => 'id',
            'filter_index' => 'main_table.id'
            )
        );

        // Add comment's post title
        $this->addColumn('post_title', array(
            'header' => $this->__('Post Title'),
            'index' => 'post_title',
            )
        );

        // column: comment subject
        $this->addColumn('subject', array(
            'header' => $this->__('Subject & Content'),
            'index' => 'subject',
            'width' => '800px',
            'renderer' => 'Evozon_Blog_Block_Adminhtml_Comment_Grid_Subject_Renderer',
            'filter_condition_callback' => array($this, '_subjectAndContentFilter')
            )
        );

        // Add comment's status
        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type' => 'options',
            'options' => Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getOptionArray()
            )
        );

        // Add comment's author name and email
        $this->addColumn('author', array(
            'header' => $this->__('Author name & email'),
            'index' => 'author',
            'renderer' => 'Evozon_Blog_Block_Adminhtml_Comment_Grid_Author_Renderer',
            'filter_condition_callback' => array($this, '_authorFilter')
            )
        );

        // Add comment's created_at date
        $this->addColumn('created_at', array(
            'header' => $this->__('Created date'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at'
            )
        );

        // Add comment's parent id
        $this->addColumn('parent_id', array(
            'header' => $this->__('Parent'),
            'index' => 'parent_id',
            'width' => '90px',
            'renderer' => 'Evozon_Blog_Block_Adminhtml_Comment_Grid_Parent_Renderer'
            )
        );

        $this->addColumn('action', array(
            'header' => Mage::helper('evozon_blog')->__('Action'),
            'filter'    => false,
            'sortable'  => true,
            'type' => 'select',
            'renderer' => 'Evozon_Blog_Block_Adminhtml_Comment_Grid_Post_Renderer',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Get the url for row edit
     * 
     * @param Evozon_Blog_Model_Comment $row
     * @return String Url for edit
     */
    public function getRowUrl($row)
    {
        // This is where row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * Mass actions for grid records 
     * 
     * @return \Evozon_Blog_Block_Adminhtml_Post_Grid
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareMassaction()
    {
        // the field with checkboxes, on which mass actions will take place
        $this->setMassactionIdField('id');

        // attribute name of the main selector which will be grabbed as post var in controller
        $this->getMassactionBlock()->setFormFieldName('comment_ids');

        // add delete post action
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => Mage::helper('evozon_blog')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => Mage::helper('evozon_blog')->__('Are you sure you want to delete this comment?')
        ));

        // get the statuses for change status action
        $statuses = Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getOptionArray();

        // show statuses to change into
        $this->getMassactionBlock()->addItem('status', array(
            'label' => Mage::helper('evozon_blog')->__('Change Status'),
            'url' => $this->getUrl('*/*/massStatus', array('_current' => true)),
            'additional' => array(
                'visibility' => array(
                    'name' => 'status',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => Mage::helper('evozon_blog')->__('Comment Status'),
                    'values' => $statuses
                )
            )
        ));

        return $this;
    }

    /**
     * Custom filter for author column
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  Evozon_Blog_Model_Resource_Post_Collection $collection
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return \Evozon_Blog_Block_Adminhtml_Comment_Grid
     */
    protected function _authorFilter($collection, $column)
    {
        // verify if the value of the filter is empty
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        // selects only the comments which match with the filter value
        $collection->getSelect()
            ->where("admin.firstname like ? OR admin.lastname like ? OR admin.email like ? "
                . "OR firstname.value like ? OR lastname.value like ? OR customer.email like ?"
                . "OR main_table.author like ? OR main_table.author_email like ?", "%$value%"
        );

        return $this;
    }

    /**
     * Custom filter for post title column
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  Evozon_Blog_Model_Resource_Post_Collection $collection
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return \Evozon_Blog_Block_Adminhtml_Comment_Grid
     */
    protected function _postFilter($collection, $column)
    {
        // verify if the value of the filter is empty
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        // selects only the comments which match with the filter value
        $collection->getSelect()->where(
            "title.value like ? ", "%$value%"
        );

        return $this;
    }

    /**
     * Custom filter for subject and content column
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  Evozon_Blog_Model_Resource_Post_Collection $collection
     * @param  Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return \Evozon_Blog_Block_Adminhtml_Comment_Grid
     */
    protected function _subjectAndContentFilter($collection, $column)
    {
        // verify if the value of the filter is empty
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        // selects only the comments which match with the filter value
        $collection->getSelect()->where(
            "main_table.subject like ? OR main_table.content like ?", "%$value%"
        );

        return $this;
    }

}
