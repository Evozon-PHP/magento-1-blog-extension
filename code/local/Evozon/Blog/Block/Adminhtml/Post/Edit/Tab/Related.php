<?php

/**
 * Load posts that can be assigned as related to other posts.
 *
 * @package     Evozon_Blog 
 * @author      Calin Florea <calin.florea@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Related extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();

        // set defaults
        $this->setId('related_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setDefaultFilter(array('in_posts' => 1));
    }

    /**
     * Prepare collection of posts: select all posts from same or all stores
     * and exclude the current post.
     * 
     * @author  Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Related
     */
    protected function _prepareCollection()
    {
        // load post collection and add basic joins
        $collection = Mage::getResourceModel('evozon_blog/post_collection')
            ->addAttributeToSelect(array('title', 'status', 'store_visibility', 'publish_date', 'created_at'))
            ->addAttributeToFilter('entity_id', array('nin' => $this->_getPost()->getId()));
        
        // get default status
        $collection->joinAttribute('default_status', 'evozon_blog_post/status', 'entity_id', null, 'left', Mage_Core_Model_App::ADMIN_STORE_ID);

        // verify if a store was selected and get data depending on the store id
        if ($this->getStoreId()) {
            $collection->joinAttribute('evozon_blog_post_visibility', 'evozon_blog_post/store_visibility', 'entity_id', null, 'inner', $this->getStoreId());
            $collection->joinAttribute('evozon_blog_post_status', 'evozon_blog_post/status', 'entity_id', null, 'inner', $this->getStoreId());
            $collection->joinAttribute('evozon_blog_post_title', 'evozon_blog_post/title', 'entity_id', null, 'inner', $this->getStoreId());
            $collection->joinAttribute('evozon_blog_post_publish_date', 'evozon_blog_post/publish_date', 'entity_id', null, 'inner', $this->getStoreId());
        }
        
        $this->setCollection($collection);
        parent::_prepareCollection();

        return $this;
    }

    /**
     * Prepare related posts grid
     * 
     * @author Calin Florea <calin.florea@evozon.com> 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Related
     */
    protected function _prepareColumns()
    {
        // the checkbox column which will allow to see/check/uncheck selected products
        $this->addColumn('in_posts', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'field_name' => 'in_posts',
            'name' => 'in_posts',
            'values' => $this->getSelectedRelatedPosts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));

        // verify is a store was selected to add columns depending on the store or not
        if ($this->getStoreId()) {
             $this->addColumn('evozon_blog_post_title', array(
                'header' => Mage::helper('catalog')->__('Title'),
                'align' => 'left',
                'index' => 'evozon_blog_post_title',
            ));
            
            $this->addColumn('evozon_blog_post_status', array(
                'header' => $this->__('Status'),
                'index' => 'evozon_blog_post_status',
                'type' => 'options',
                'options' => Mage::getModel('evozon_blog/adminhtml_post_status')->getAllOptions()
            ));

            $this->addColumn('evozon_blog_post_visibility', array(
                'header' => $this->__('Visibility on store'),
                'index' => 'evozon_blog_post_visibility',
                'type' => 'options',
                'options' => Mage::getModel('adminhtml/system_config_source_yesno')->toArray()
            ));
            
            // add post's publish date
            $this->addColumn('evozon_blog_post_publish_date', array(
                'header' => $this->__('Publish date'),
                'index' => 'evozon_blog_post_publish_date'
            ));
        } else {
            // name of the post
            $this->addColumn('title', array(
                'name' => 'title',
                'header' => Mage::helper('catalog')->__('Title'),
                'align' => 'left',
                'index' => 'title'
            ));

            // add post's default status
            $this->addColumn('default_status', array(
                'header' => $this->__('Status'),
                'index' => 'default_status',
                'type' => 'options',
                'options' => Mage::getModel('evozon_blog/adminhtml_post_status')->getAllOptions()
            ));
            
            // add post's publish date
            $this->addColumn('related_publish_date', array(
                'header' => $this->__('Publish date'),
                'index' => 'publish_date'
            ));
        }

        return $this;
    }

    /**
     * Get grid url from post
     * 
     * @return string
     * @author Calin Florea <calin.florea@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/relatedgrid', array(
            'id' => $this->_getPost()->getId(), 
            'store' => $this->getStoreId()
        ));
    }

    /**
     * Return post model registered in controller
     * 
     * @return \Evozon_Blog_Model_Post
     * @author Calin Florea <calin.florea@evozon.com>
     */
    protected function _getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * Accessing store id
     * 
     * @return int
     */
    protected function getStoreId()
    {
        return (int) $this->getRequest()->getParam('store', 0);
    }

    /**
     * Get selected posts from $_POST if is set, if not get them from DB
     * 
     * @return \Evozon_Blog_Model_Related
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function getSelectedRelatedPosts()
    {
        // @see Evozon_Blog_Adminhtml_Blog_PostController::relatedAction - we setRelatedPosts over there.
        $relatedPosts = $this->getRelatedPosts();

        // if array is empty, then the selected related objects from object model
        if (empty($relatedPosts)) {
            $relatedPosts = (array) $this->_getPost()->getSelectedRelatedPosts();
        }

        return $relatedPosts;
    }

    /**
     * Get array of related id's, if empty get them from db.
     *  
     * @param $column
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Related
     * @author Calin Florea <calin.florea@evozon.com>
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_posts') {
            $relatedIds = $this->getSelectedRelatedPosts();
            if (empty($relatedIds)) {
                $relatedIds = 0;
            }
            
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $relatedIds));
            } else {
                if ($relatedIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $relatedIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        
        return $this;
    }

}
