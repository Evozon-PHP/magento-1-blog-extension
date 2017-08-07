<?php

/**
 * Post frontend controller
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 * @author     Murgocea Victorita <victorita.murgocea@evozon.com>
 */
require_once 'Evozon/Blog/controllers/LayoutController.php';
class Evozon_Blog_PostController extends  Evozon_Blog_LayoutController
{

    /**
     * Set every action result as being blog type
     * 
     * @return \Evozon_Blog_PostController
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::registry('is_blog')) {
            Mage::register('is_blog', true);
        }

        return $this;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Init Post
     *
     * @access protected
     * @return Evozon_Blog_Model_Post
     */
    protected function _initPost()
    {
        $postId = $this->getRequest()->getParam('id', 0);

        $post = Mage::getModel('evozon_blog/post')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($postId);

        if (
            !$post->getId() 
            || $post->getStoreVisibility() != Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED
            || $post->getStatus() != Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED
            || !$post->isVisibleOnWebsite((int) Mage::app()->getWebsite()->getId()) 
            || ($post->getId() && $post->getIsRestricted())
        ) {
            return false;
        }

        Mage::register('blog_post', $post);
        return $post;
    }

    /**
     * View post action
     * Applies custom design for post if it has it set
     * 
     * @access public
     * @return void
     */
    public function viewAction()
    {
        $post = $this->_initPost();
        
        if (!$post)
        {
            return $this->_forward('no-route');
        }
        
        if ($post->getCustomUseDefaultSettings()) {
            $this->loadLayout();
        } else {
            $this->getPostLayout($post->getDesignSettings(), $post->getId());
        }
        
        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('blog-post blog-post' . $post->getId());
        }

        $this->renderLayout();
    }

    /**
     * Preview action
     * 
     * @return void
     */
    public function previewAction()
    {
        $postId = $this->getRequest()->getParam('id', 0);
        $previewKey = $this->getRequest()->getParam('previewKey', 0);

        $post = Mage::getModel('evozon_blog/post')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($postId);

        try {
            if (!$post || !$post->getId()) {
                Mage::throwException('Invalid post id');
            }
            $validateModel = Mage::getModel(
                'evozon_blog/post_preview_validate',
                $postId
            );
            $validateModel->validatePreviewKeyWithException($previewKey);
        } catch (Exception $exc) {
            Mage::logException($exc);
            return $this->_forward('no-route');
        }

        Mage::register('blog_post', $post);

        if ($post->getCustomUseDefaultSettings()) {
            $this->loadLayout();
        } else {
            $this->getPostLayout($post->getDesignSettings(), $post->getId());
        }

        if ($root = $this->getLayout()->getBlock('root')) {
            $root->addBodyClass('blog-post blog-post' . $post->getId());
        }

        $this->renderLayout();
    }

}
