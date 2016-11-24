<?php

/**
 * Add Blog comment author details
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Comment_Edit_Tab_Author extends Mage_Adminhtml_Block_Widget_Form
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

        $form = new Varien_Data_Form([
            'id'        => 'edit_form',            
            'action' => $this->getData('action'),
            'method'    => 'post'
        ]);        

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => $_helper->__('Author details')));                
                
        // show author name
        $fieldset->addField('author', 'note', array(
            'label' =>  $_helper->__('Author name'),
            'text'     => $model->getAuthor()->getName(),
        ));
        
        // show author email
        $fieldset->addField('author_email', 'note', array(
            'label' =>  $_helper->__('Author email'),
            'text'     => $model->getAuthor()->getEmail(),
        ));
        
        // show author ip
        $fieldset->addField('author_ip', 'note', array(
            'label' =>  $_helper->__('Author IP'),
            'text'     => $model->getAuthorIp(),
        ));
               
        // set form data.
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
