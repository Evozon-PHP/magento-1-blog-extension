<?php

/**
 * List, Edit, Add New and Mass actions for posts.
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Blog_PostController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Initial, default action
     * shows posts' grid
     */
    public function indexAction()
    {
        $this->_title($this->__('Blog Posts'))
            ->_title($this->__('Blog Posts'));
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Grid action to apply sorting and filtering to the posts grid
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Initialize action
     * set the breadcrumbs and the active menu
     * Make the active menu match the menu config nodes (without 'children' inbetween)
     * 
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('evozon_blog/evozon_blog_post')
            ->_title($this->__('BLOG'))->_title($this->__('Posts'));

        return $this;
    }

    /**
     * New post action
     * forward to edit and do the logic overthere
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit post action 
     * render form and data to be edited
     * 
     * @return null if the id provided cannot be loaded
     */
    public function editAction()
    {
        $postId = $this->getRequest()->getParam('id', false);
        $post = $this->_initPost();
        $isSingleStore = Mage::app()->isSingleStoreMode() ? : false;

        if (is_int($postId) && ($post instanceof Evozon_Blog_Model_Post && !$post->getId()) || null === $post) {
            $this->_getSession()->addError(
                Mage::helper('evozon_blog')->__('This post no longer exists.')
            );

            $this->_redirect('*/*/');
            return;
        }

        $this->_title($post->getTitle());
        $this->loadLayout();

        if (!$post instanceof Evozon_Blog_Model_Post || !$post->getId()) {
            $this->getLayout()->getBlock('left')->unsetChild('store_switcher');
        }

        $switchBlock = $this->getLayout()->getBlock('store_switcher');
        if (false === $isSingleStore && $switchBlock instanceof Mage_Adminhtml_Block_Store_Switcher) {
            $switchBlock
                ->setDefaultStoreName(Mage::helper('evozon_blog')->__('Default Values'))
                ->setWebsiteIds($post->getWebsiteIds())
                ->setSwitchUrl(
                    $this->getUrl(
                        '*/*/*',
                        array(
                        '_current' => true,
                        'active_tab' => null,
                        'tab' => null,
                        'store' => null
                        )
                    )
            );
        }

        Mage::register('evozon_current_blog_post_restriction_rules',
            $post->getRestrictions());

        $this->renderLayout();
    }

    /**
     * Initialize post saving
     * Processing before-save data from additional tabs
     * Data that is prepared to be saved: relations (website, categories, products, related posts, tags and restrictions)
     *
     * @return \Evozon_Blog_Model_Post
     */
    protected function _initPostSave()
    {
        $post = $this->_initPost();
        $postData = $this->getRequest()->getPost('post');
        $restrictionsForm = $this->getRequest()->getPost(
            Evozon_Blog_Model_Restriction::RESTRICTIONS_PREFIX,
            false);

        /**
         * Websites
         */
        if (!isset($postData['website_ids']) && $post->getStoreId() == 0) {
            $postData['website_ids'] = array();
        }

        $post->addData($postData);

        if (Mage::app()->isSingleStoreMode()) {
            $post->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }

        if ($post->getId() === null) {
            $post->setAdminId(Mage::getSingleton('admin/session')->getUser()->getUserId());
        }

        /**
         * Check "Use Default Value" checkboxes values
         */
        if ($useDefaults = $this->getRequest()->getPost('use_default')) {
            foreach ($useDefaults as $attributeCode) {
                $post->setData($attributeCode, false);
            }
        }

        $categories = $this->getRequest()->getPost('category_ids', -1);
        if (-1 !== $categories) {
            $categories = explode(',', $categories);
            $categories = array_unique($categories);
            $post->setCategoryIds($categories);
        }

        $products = $this->getRequest()->getPost('products', -1);
        if ($products != -1) {
            $post->setRelatedProducts(Mage::helper('adminhtml/js')->decodeGridSerializedInput($products));
        }

        $relatedPosts = $this->getRequest()->getPost('related_posts', -1);
        if ($relatedPosts != -1) {
            $post->setRelatedPosts(Mage::helper('adminhtml/js')->decodeGridSerializedInput($relatedPosts));
        }

        $relatedTags = $this->getRequest()->getPost('selected-tags-ids');
        if (!empty($relatedTags)) {
            $relatedTags = explode(',', $relatedTags);
            $relatedTags = array_diff($relatedTags, array(""));
            $post->setRelatedTags($relatedTags);
        }

        if (false !== $restrictionsForm) {
            $post->setRestrictionForm($restrictionsForm);
        }

        return $post;
    }

    /**
     * Save post action
     * Save data into database
     */
    public function saveAction()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        $postId = $this->getRequest()->getParam('id');
        $redirectBack = $this->getRequest()->getParam('back', false);

        $data = $this->getRequest()->getPost();
        if (is_array($data)) {
            $post = $this->_initPostSave();
            $post->setAttributeSetId($post->getDefaultAttributeSetId());

            try {
                $post->save();
                $postId = $post->getId();

                $this->_getSession()->addSuccess(
                    Mage::helper('evozon_blog')->__('The post has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($e->getMessage())->setPostData($post);

                $redirectBack = true;
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()
                    ->addError(
                        Mage::helper('evozon_blog')->__('An error occurred while saving the post.')
                    )
                    ->setPostData($data);

                $redirectBack = true;
            }
        }
        
        if ($redirectBack) {
            $this->_redirect(
                '*/*/edit',
                array(
                'id' => $postId,
                '_current' => true
                )
            );
        } else {
            $this->_redirect('*/*/', array('store' => $storeId));
        }
    }

    /**
     * Initialization of model & register.
     * 
     * @return \Evozon_Blog_Adminhtml_Blog_PostController
     */
    protected function _initPost()
    {
        $postModel = Mage::getModel('evozon_blog/post');
        $postId = (int) $this->getRequest()->getParam('id', false);
        $storeId = $this->getRequest()->getParam('store', 0);

        $this->_title($this->__('Blog Posts'))
            ->_title($this->__('Manage Posts'));

        // load category model by given id
        try {
            if (!$postModel instanceof Evozon_Blog_Model_Post) {
                throw new Exception('No post model');
            }

            $postModel->setStoreId($storeId);
            $postModel->setData('_edit_mode', true);

            if (false !== $postId && is_integer($postId)) {
                $postModel->load($postId);
            }
        } catch (Exception $exc) {
            Mage::logException($exc);

            return false;
        }

        Mage::register('evozon_blog', $postModel);
        return $postModel;
    }

    /**
     * Delete post from database
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function deleteAction()
    {
        $_helper = Mage::helper('evozon_blog');
        $postId = $this->getRequest()->getParam('id');
        if (empty($postId) || !is_numeric($postId)) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('No post id provided for delete action!'));
            $this->_redirect('*/*/');

            return $this;
        }

        $postModel = Mage::getModel('evozon_blog/post');
        $postModel->load($postId);
        try {
            $postModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($_helper->__('The post with id %s has been deleted!',
                    $postId));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('An error occurred while deleting the post.'));
            Mage::logException($ex);
        }

        $this->_redirect('*/*/');
    }

    /**
     * Load category tree action   
     * return json with child categories
     * It is called from expanding the categories tree in Categories tab from Edit Post view
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>    
     */
    public function categoriesJsonAction()
    {
        $this->_initPost();
        $this->getResponse()
            ->setBody(
                $this->getLayout()
                ->createBlock(
                    'evozon_blog/adminhtml_post_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    /**
     * Load category tree action 
     * category tree will be shown in a tab of post record
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com> 
     */
    public function categoriesAction()
    {
        $this->_initPost();
        $this->loadLayout()
            ->renderLayout();
    }

    /**
     * Products action
     * It is rendered when accesing Products tab on Edit Post view
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function productsAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('post.edit.tab.product')
            ->setRelatedProducts($this->getRequest()->getPost('related_products', null));
        $this->renderLayout();
    }

    /**
     * Grid action
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function productsGridAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('post.edit.tab.product')
            ->setRelatedProducts($this->getRequest()->getPost('related_products', null));
        $this->renderLayout();
    }

    /**
     * Ajax load related posts grid in Related Posts tab on Edit Post view
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function relatedgridAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('evozon.blog.post.related')
            ->setRelatedPosts($this->getRequest()->getPost('related', null));
        $this->renderLayout();
    }

    /**
     * Related posts action in Related Posts tab on Edit Post action
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function relatedAction()
    {
        $this->_initPost();
        $this->loadLayout();
        $this->getLayout()->getBlock('evozon.blog.post.related')
            ->setRelatedPosts($this->getRequest()->getPost('related', null));
        $this->renderLayout();
    }

    /**
     * Mass delete action from grid
     * deletes all selected posts
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massDeleteAction()
    {
        $postIds = $this->getRequest()->getParam('posts');
        if (!is_array($postIds) || empty($postIds)) {
            $this->_getSession()->addError($this->__('Please select blog post(s).'));
            $this->_redirect('*/*/index');
        }

        try {
            foreach ($postIds as $postId) {
                $post = Mage::getSingleton('evozon_blog/post')
                    ->load($postId)
                    ->setRewriteToBeRemoved(true);

                $post->delete();
            }

            $object = new Varien_Object($postIds);
            Mage::getSingleton('index/indexer')
                ->processEntityAction($object, Evozon_Blog_Model_Post::ENTITY, Mage_Index_Model_Event::TYPE_DELETE);

            $this->_getSession()->addSuccess(
                $this->__('Total of %d post(s) have been deleted.', count($postIds))
            );
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Update post(s) status action
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massStatusAction()
    {
        $status = (int) $this->getRequest()->getParam('status');

        $this->massResponse('status', $status,
            'An error occurred while updating the post(s) status.');
        $this->_redirect('*/*/',
            array('store' => (int) $this->getRequest()->getParam('store', 0)));
    }

    /**
     * Update post(s) visibility action
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massVisibilityAction()
    {
        $visibility = (int) $this->getRequest()->getParam('visibility');

        $this->massResponse('store_visibility', $visibility,
            'An error occurred while updating the post(s) store visibility.');
        $this->_redirect('*/*/',
            array('store' => (int) $this->getRequest()->getParam('store', 0)));
    }

    /**
     * Try-catch construction for the mass update actions
     * It also triggers the reindex (if it has to)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $attribute
     * @param int|string $value
     * @param string $errorMessage
     */
    protected function massResponse($attribute, $value, $errorMessage)
    {
        $entityIds = (array) $this->getRequest()->getParam('posts');
        $storeId = (int) $this->getRequest()->getParam('store', 0);

        if (!$storeId) {
            $this->_getSession()->addNotice($this->__('The update made from "All Store Views" will only affect the posts that have the "Use Default Value" checked!'));
        }

        try {
            foreach ($entityIds as $postId) {
                $post = Mage::getSingleton('evozon_blog/post')
                    ->setStoreId($storeId)
                    ->load($postId)
                    ->setData($attribute, $value)
                    ->setMassActionReindexed(true);

                $post->save();
            }

            $object = new Varien_Object(array(
                'post_ids' => array_unique($entityIds),
                'attributes' => array($attribute=>$value),
                'store_id' => $storeId
            ));

            Mage::getSingleton('index/indexer')->processEntityAction(
                $object, Evozon_Blog_Model_Post::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
            );

            $this->_getSession()->addSuccess(
                $this->__('Total of %d post(s) have been updated.', count($entityIds))
            );
        } catch (Mage_Core_Model_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $errorMessage);
        }
    }

    /**
     * Create a new restriction html control
     */
    public function newRestrictionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $typeArr = explode('|',
            str_replace('-', '/', $this->getRequest()->getParam('type', array())));
        $type = $typeArr[0];
        $html = '';
        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('evozon_blog/restriction'));

        if ($model instanceof Evozon_Blog_Model_Restriction_Rule_Interface_Rule) {
            $html = Mage::helper('evozon_blog/post')
                ->getRestrictionRendererBlock($model)
                ->renderView();
        }

        $this->getResponse()->setBody($html);
    }
}
