<?php

/**
 * Add Blog comment general information
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment_Edit_Tab_Details extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * create new/edit form for Details tab
     * 
     * @return Mage_Adminhtml_Block_Widget_Form
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _prepareForm()
    {
        // get model loaded and registered in controller
        $model = Mage::registry('evozon_blog_comment');

        // get module's helper for translations and other functions
        $_helper = Mage::helper('evozon_blog');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $postFieldset = $form->addFieldset('post_fieldset', array('legend' => $_helper->__('Post details')));

        // show a link to the post
        $postFieldset->addField('post_title', 'note', array(
            'name' => 'post_title',
            'label' => $_helper->__('Post Title'),
            'title' => $_helper->__('Post Title'),
            'text' =>
            ' <a href="' . Mage::helper("adminhtml")->getUrl('*/blog_post/edit', array('id' => $model->getPostId(), 'active_tab' => 'comments')) . '">'
            . $this->getSelectedPostTitle($model->getPostId())
            . '</a>',
            'after_element_html' => '<br /><small>' .
            $_helper->__('Access the above link to see all the post comments.') .
            '</small>',
        ));
        
        // create an input hidden for the post_id 
        $postFieldset->addField('post_id', 'hidden', array(
            'name'    => 'post_id',
            'label'   => $_helper->__('Post Title')          
        ));


        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $_helper->__('Details')));

        $fieldset->addField('id', 'hidden', array(
            'name' => 'id',
        ));

        $fieldset->addField('status', 'select', array(
            'name' => 'status',
            'label' => $_helper->__('Status'),
            'title' => $_helper->__('Status'),
            'values' => Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getOptionArray(),
            'class' => 'required-entry',
            'required' => true,
            'style' => 'width: 405px;',
            'after_element_html' => '<br /><small>' .
            $_helper->__('After saving the status to "Approved" , you`ll be able to add a reply.') .
            '</small>',
        ));

        // comment subject.
        $fieldset->addField('subject', 'text', array(
            'name' => 'subject',
            'label' => $_helper->__('Comment subject'),
            'title' => $_helper->__('Comment subject'),
            'required' => Mage::getSingleton('evozon_blog/config')->getCommentsValidationConfig('subject') ? true : false,
            'style' => 'width: 400px;'
        ));

        // comment content. 
        $fieldset->addField('content', 'textarea', array(
            'name' => 'content',
            'label' => $_helper->__('Comment'),
            'title' => $_helper->__('Comment'),
            'required' => true,
            'class' => 'required-entry',
            'style' => 'width: 400px;',
        ));

        // show created at date.
        $fieldset->addField('created_at', 'note', array(
            'label' => $_helper->__('Created date'),
            'text' => Mage::helper('evozon_blog')->getLocaleDate($model->getCreatedAt())
        ));

        // set form data.
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return the title of the post for the current comment
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $postId
     * @return string
     */
    public function getSelectedPostTitle($postId)
    {
        // get the post collection
        $collection = Mage::getModel('evozon_blog/post')->getCollection()
            ->addAttributeToFilter('entity_id', array('eq' => (int) $postId))
            ->addAttributeToSelect('title');

        // return the title of the post
        return $collection->getFirstItem()->getTitle();
    }

}
