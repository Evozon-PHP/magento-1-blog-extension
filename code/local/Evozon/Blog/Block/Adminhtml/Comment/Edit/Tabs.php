<?php

/**
 * Create tabs for comment entry for keeping post data organized.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    /**
     * initialize some of the data needed for creating tabs
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('comment_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('evozon_blog')->__('Comment Information'));
    }

    /**
     * Add tabs for Blog Comment for organizing comment content.
     * 
     * @return Mage_Adminhtml_Block_Widget_Tabs
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _beforeToHtml()
    {
        // get the model registered in controler
        $model = Mage::registry('evozon_blog_comment');

        // add tab: subject, content, post_id, created_at ... etc.
        $this->addTab('details', array(
            'label' => Mage::helper('evozon_blog')->__('Details'),
            'title' => Mage::helper('evozon_blog')->__('Details'),
            'content' => $this->getLayout()
                ->createBlock('evozon_blog/adminhtml_comment_edit_tab_details')
                ->toHtml(),
            'active' => true
        ));

        // if we edit a record, add also info about author details.
        if ($model->getId()) {
            // add tab: author details
            $this->addTab('author', array(
                'label' => Mage::helper('evozon_blog')->__('Author details'),
                'title' => Mage::helper('evozon_blog')->__('Author details'),
                'content' => $this->getLayout()
                    ->createBlock('evozon_blog/adminhtml_comment_edit_tab_author')
                    ->toHtml(),
            ));

            // if exists subcomments show the tab
            if ($model->getFirstLevelCount()) {
                $this->addTab('subcomments', array(
                    'label' => Mage::helper('evozon_blog')->__('Sub-comments'),
                    'title' => Mage::helper('evozon_blog')->__('Sub-comments'),
                    'content' => $this->getLayout()
                        ->createBlock('evozon_blog/adminhtml_comment_list')
                        ->setCommentId($model->getId())
                        ->toHtml(),
                ));
            }

            // add tab: subject, content, post_id, created_at ... etc for subcomment
            if ($model->getStatus() == Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED) {
                $this->addTab('reply', array(
                    'label' => Mage::helper('evozon_blog')->__('Reply'),
                    'title' => Mage::helper('evozon_blog')->__('Reply'),
                    'content' => $this->getLayout()
                        ->createBlock('evozon_blog/adminhtml_comment_edit_tab_reply')
                        ->toHtml()
                ));
            }
        }

        return parent::_beforeToHtml();
    }

}
