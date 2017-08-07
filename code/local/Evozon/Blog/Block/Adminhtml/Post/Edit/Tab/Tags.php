<?php

/**
 * Load the blog tags in a special input field via autocomplete
 * Allows to add new tags and complete each store version of it
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Tags extends Mage_Adminhtml_Block_Widget
{

    /**
     * IDs of the tags already related to the tag
     * @var array 
     */
    protected $_selectedTags = array();

    /**
     * Setting the template to render the field
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/edit/tab/tags.phtml';
    }

    /**
     * Getting post from registry
     * 
     * @return Evozon_Blog_Model_Post
     */
    protected function getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        if ($this->getRequest()->getParam('store')) {
            return $this->getRequest()->getParam('store');
        }

        return 0;
    }

    /**
     * Set the hidden input with the already existing selected tags ids
     * This hidden input keeps selected tags that will be saved afterwards
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getSelectedTagsIds()
    {
        return implode(',', $this->_getSelectedTags());
    }

    /**
     * Generate the post selected tags as JSON
     *
     * @return string
     */
    public function getSelectedTagsJson()
    {
        $selectedTags = $this->getSelectedTags()->load()
            ->toArray(array('entity_id', 'name'));

        return Mage::helper('core')->jsonEncode($selectedTags);
    }

    /**
     * Accessing the tag-post relations table and getting connected posts
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array | \Evozon_Blog_Model_Resource_Tag_Collection
     */
    protected function getSelectedTags()
    {
        $selectedTags = $this->_getSelectedTags();

        $tagsCollection = Mage::getModel('evozon_blog/tag')
            ->getCollection()
            ->setStoreId($this->getPost()->getStoreId())
            ->addAttributeToSelect(array('name', 'count'), 'inner')
            ->addAttributeToFilter('entity_id', array('in' => $selectedTags));

        return $tagsCollection;
    }

    /**
     * Accessing db and retrieving array of ids related to post
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function _getSelectedTags()
    {
        if (empty($this->_selectedTags)) {
            $this->_selectedTags = $this->getPost()->getSelectedRelatedTags();
        }

        return $this->_selectedTags;
    }

    /**
     * Ajax call is sent to this action
     * in order to display the list of tags matching the user input that has beem given
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getTagLoadActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/blog_tag/loadTags');
    }

    /**
     * If the user intends to add a new tag in the database itself,
     * then the editing template from Evozon_Blog_Block_Adminhtml_Tag_Edit_Tab_Tags_Options will be called
     * and the user will be allowed to edit values for the rest of the stores, to make it accessible
     * for further use
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function addNewTagBlockHtml()
    {
        return $this->getLayout()->createBlock('evozon_blog/adminhtml_post_edit_tab_tags_options')->toHtml();
    }

    /**
     * Action used to save the new added tag to database
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getSaveTagActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('*/blog_tag/saveTag');
    }

}
