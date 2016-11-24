<?php

/**
 * List, Edit, Add New and Mass actions for tags.
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Blog_TagController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Stores the tags collection in order not to make the joins every other time
     * @var Evozon_Blog_Model_Resource_Tag_Collection
     */
    protected $_tagsCollection = NULL;

    /**
     * Initial, default action
     * show post's grid
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    /**
     * Grid action to apply sorting and filtering to the tags grid
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
     * Set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('evozon_blog/evozon_blog_tag')
            ->_title($this->__('BLOG'))->_title($this->__('Blog Tags'));

        return $this;
    }

    /**
     * New tag action
     * Forward to edit and do the logic overthere
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function newAction()
    {
        // just forward the new action to a blank edit form
        $this->_forward('edit');
    }

    /**
     * Edit tag action
     * Render form and data to be edited
     * If there is data in session [like having errors and the data is not saved],
     * We will use that data on the model
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return NULL if the id provided cannot be loaded
     */
    public function editAction()
    {
        $tagId = (int) $this->getRequest()->getParam('id');
        $model = $this->_initTag();

        if ($tagId && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('evozon_blog')->__('This tag no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        $this->_title($model->getId() ? $model->getName() : $this->__('New Tag'));

        $data = Mage::getSingleton('adminhtml/session')->getTagData(true);
        if (!empty($data)) {
            $model->setData('edit', $data);
        }

        Mage::register('evozon_blog_tag', $model);

        $this->_initAction()
            ->_addBreadcrumb($model->getId() ? $this->__('Edit Tag') : $this->__('New Tag'), $model->getId() ? $this->__('Edit Tag') : $this->__('New Tag'))
            ->renderLayout();
    }

    /**
     * Initialize new tag model
     * or load an existing one if the paramter allows
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Tag
     */
    protected function _initTag()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('evozon_blog/tag')
            ->setStoreId(0);

        if ($id) {
            $model->load($id);
        }

        return $model;
    }

    /**
     * Save tag entity into database
     * Before proceeding to the save, we have to check that needed values are set
     * Validation rules: default value is required; any value on the specific store has to be unique
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function saveAction()
    {
        $tag = $this->_initTag();
        $tagData = $this->getRequest()->getPost();

        try {
            $error = false;
            $this->filter($tag, $tagData['name']);
            $tag->save();

            $this->_getSession()->addSuccess(
                Mage::helper('evozon_blog')->__('Tag was successefully saved.')
            );
        } catch (Evozon_Blog_Model_Exception_Validate $exc) {
            foreach($exc->getExceptionMessages() as $message) {
                $this->_getSession()->addError($message);
            }
            $error = true;
        } catch (Mage_Core_Exception $exc) {
            Mage::logException($exc);
            $this->_getSession()->addError($exc->getMessage());
            $error = true;
        }

        if ($error) {
            $this->_getSession()->setTagData($tagData['name']);
            $this->_redirect('*/*/edit', array(
                'id' => $tag->getId(),
                '_current' => true
                )
            );
        } else {
            $this->_redirect('*/*/');
        }
    }

    /**
     * Presentation layer validation
     * It will trim, striptag and remove HtmlEntities from the input
     * It will check that the default value has been set
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Tag $tag
     * @param array $input
     * @return array
     * @throws Evozon_Blog_Model_Exception_Validate
     */
    protected function filter($tag, $input)
    {
        $validator = Mage::getModel('evozon_blog/filter_factory', 
            array(
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_TYPE => Evozon_Blog_Model_Filter_Config::XML_PATH_BLOG_VALIDATION_TYPE_TAGS,
                Evozon_Blog_Model_Filter_Factory::EVOZON_BLOG_MODEL_FILTER_INPUT => $input               
            )
        );

        if ($validator->hasMessages()) {
            throw new Evozon_Blog_Model_Exception_Validate($validator->getMessages());
        }

        $tag->setInputData($validator->getEscaped());

        return $this;
    }

    /**
     * Delete tag from database
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function deleteAction()
    {
        $_helper = Mage::helper('evozon_blog');
        $tagId = $this->getRequest()->getParam('id');
        if (empty($tagId) || !is_numeric($tagId)) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('No tag id provided for delete action!'));
            $this->_redirect('*/*/');
            return $this;
        }

        $postModel = Mage::getModel('evozon_blog/tag')->load($tagId);
        try {
            $postModel->delete();
            Mage::getSingleton('adminhtml/session')->addSuccess($_helper->__('Tag with id %s successfully deleted!', $tagId));
        } catch (Exception $exc) {
            Mage::getSingleton('adminhtml/session')->addError($_helper->__('Error when deleting tag: %s.', $exc->getMessage()));
            Mage::logException($exc);
        }

        $this->_redirect('*/*/');
    }

    /**
     * Mass delete action from grid
     * Deletes all selected tags
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function massDeleteAction()
    {
        $tagIds = $this->getRequest()->getParam('tags');
        if (!is_array($tagIds)) {
            $this->_getSession()->addError($this->__('Please select blog tag(s).'));
        } else {
            if (!empty($tagIds)) {
                try {
                    foreach ($tagIds as $tagId) {
                        $post = Mage::getSingleton('evozon_blog/tag')->load($tagId);
                        $post->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d tag(s) have been deleted.', count($tagIds))
                    );
                } catch (Exception $exc) {
                    Mage::logException($exc);
                    $this->_getSession()->addError($exc->getMessage());
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Accesses Tag collection and searches through tag names the values like the user given input
     * Returns an array that looks like 
     * array(array($id, $name, count), array($id, $name, count),..);
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function loadTagsAction()
    {
        try {
            $input = $this->getRequest()->getParam('term');

            $response = array();
            $results = Mage::getResourceModel('evozon_blog/tag_collection')
                ->setStoreId($this->getRequest()->getParam('store'))
                ->addAttributeToSelect(array('entity_id', 'name', 'count'))
                ->filterByInput($input);

            foreach ($results as $result) {
                $response[] = array($result->getId(), $result->getName(), $result->getCount() ? $result->getCount() : 0);
            }

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } catch (Mage_Core_Exception $e) {
            $result = array('error' => true, 'message' => $e->getMessage());
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Action that will be called from Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Tags via AJAX
     * Receives data and returns response via AJAX to the form
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Tags
     * @return JSON
     */
    public function saveTagAction()
    {
        $data = $this->getRequest()->getParam('tag');
        $tag = Mage::getModel('evozon_blog/tag');
        $response = array();

        try {
            $input = array();
            parse_str($data, $input);

            $this->filter($tag, $input);
            $tag->save();
            $response['succes'][] = $this->__('The new tag has been added!');
            $response['id'] = $tag->getId();
        } catch (Evozon_Blog_Model_Exception_Validate $exc) {
            foreach ($exc->getMessagesArray() as $exception) {
                $response['error'][] = $exception;
            }
        } catch (Mage_Core_Exception $exc) {
            Mage::logException($exc);
            $response['error'][] = $this->__('There has been an error. Please try again!');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }
}
