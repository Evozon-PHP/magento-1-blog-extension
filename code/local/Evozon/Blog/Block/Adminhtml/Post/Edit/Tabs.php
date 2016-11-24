<?php

/**
 * Generate tabs for post edit/new actions
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();

        $this->setId('post_info_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('evozon_blog')->__('Post Information'));
    }

    /**
     *  Preparing tabs to be displayed
     */
    protected function _prepareLayout()
    {
        $setId = $this->getPost()->getDefaultAttributeSetId();

        $groupCollection = Mage::getResourceModel('eav/entity_attribute_group_collection')
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();

        foreach ($groupCollection as $group) {
            $attributes = Mage::getResourceModel('evozon_blog/post_attribute_collection')
                ->setAttributeGroupFilter($group->getId())
                ->addVisibleFilter()
                ->load();

            if (count($attributes) == 0) {
                continue;
            }

            $this->addTab('group_' . $group->getId(), array(
                'label' => Mage::helper('evozon_blog')->__($group->getAttributeGroupName()),
                'content' => $this->_translateHtml($this->getLayout()->createBlock('evozon_blog/adminhtml_post_edit_tab_attributes')
                        ->setGroup($group)
                        ->setAttributes($attributes)
                        ->toHtml()),
            ));
        }

        // add tab: tags 
        $this->addTab('tags', array(
            'label' => Mage::helper('evozon_blog')->__('Tags'),
            'title' => Mage::helper('evozon_blog')->__('Tags'),
            'content' => $this->getLayout()
                ->createBlock('evozon_blog/adminhtml_post_edit_tab_tags')
                ->toHtml(),
        ));

        // add tab: comments - grid of post comments ... only if post is not new
        if (Mage::registry('evozon_blog')->getId()) {
            $this->addTab('comments', array(
                'label' => Mage::helper('evozon_blog')->__('Comments'),
                'title' => Mage::helper('evozon_blog')->__('Comments'),
                'content' => $this->getLayout()
                    ->createBlock('evozon_blog/adminhtml_comment_list')
                    ->setPostId(Mage::registry('evozon_blog')->getId())
                    ->setComments(Mage::registry('evozon_blog')->hasComments())
                    ->toHtml(),
            ));
        }

        /**
         * Don't display website for other stores than default
         */
        $store = (int) $this->getRequest()->getParam('store', 0);
        if ($store == 0 && !Mage::app()->isSingleStoreMode()) {
            $this->addTab('websites', array(
                'label' => Mage::helper('evozon_blog')->__('Websites'),
                'content' => $this->_translateHtml($this->getLayout()
                        ->createBlock('evozon_blog/adminhtml_post_edit_tab_websites')->toHtml()),
            ));
        }

        // add tab: products - grid of products to link the post with a list of products
        if ($this->getPost()->getId()) {
            $this->addTab('products', array(
                'label' => Mage::helper('evozon_blog')->__('Products'),
                'title' => Mage::helper('evozon_blog')->__('Products'),
                'url' => $this->getUrl('*/*/products', array('_current' => true)),
                'class' => 'ajax'
            ));
        }

        // add tab: categories - categories tree to link the post with product categories.
        $this->addTab('categories', array(
            'label' => Mage::helper('evozon_blog')->__('Categories'),
            'title' => Mage::helper('evozon_blog')->__('Categories'),
            'url' => $this->getUrl('*/*/categories', array('_current' => true)),
            'class' => 'ajax',
        ));

        // add tab: Related Posts - grid of articles to link post to related articles
        if ($this->getPost()->getId()) {
            $this->addTab('related', array(
                'label' => Mage::helper('evozon_blog')->__('Related Posts'),
                'title' => Mage::helper('evozon_blog')->__('Related Posts'),
                'url' => $this->getUrl('*/*/related', array('_current' => true)),
                'class' => 'ajax',
            ));
        }

        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }

    /**
     * @return Evozon_Blog_Model_Post
     */
    protected function getPost()
    {

        return Mage::registry('evozon_blog');
    }

}
