<?php

/**
 * Reply block
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Comment_Edit_Tab_Reply extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Create form for reply tab
     *  
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        // get model loaded
        $model = Mage::registry('evozon_blog_comment');
        
        // get module's helper
        $_helper = Mage::helper('evozon_blog');

        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',            
            'action' => $this->getData('action'),
            'method' => 'post'
        ));        

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $_helper->__('Reply')));
        
       // add status fieldset
        $fieldset->addField('child_status', 'select', array(
            'name'     => 'subcomment[status]',
            'label'    => $_helper->__('Status'),
            'title'    => $_helper->__('Status'),
            'values'   => Mage::getSingleton('evozon_blog/adminhtml_comment_status')->getOptionArray(),
            'required' => false,
            'style'    => 'width: 400px;',
        ));
        
        // comment subject
        $fieldset->addField('child_subject', 'text', array(
            'name'               => 'subcomment[subject]',            
            'label'              => $_helper->__('Comment subject'),
            'title'              => $_helper->__('Comment subject'),
            'after_element_html' => '<br /><small>'.$_helper->__('Enter comment subject, max. 255 characters long.').'</small>',
            'required'           => false,
            'style'              => 'width: 400px;'            
        ));                
        
        // comment content
        $fieldset->addField('child_content', 'textarea', array(
            'name'               => 'subcomment[content]',
            'label'              => $_helper->__('Comment'),
            'title'              => $_helper->__('Comment'),            
            'required'           => false,
            'after_element_html' => '<br /><small>'.$_helper->__('Fill in comment content.').'</small>',            
            'style'              => 'width: 400px;',
        ));
        
        // create an input hidden for the post_id 
        $fieldset->addField('post_id', 'hidden', array(
            'name'    => 'subcomment[post_id]',
            'label'   => $_helper->__('Post Title')          
        ));
               
        // set form data
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
