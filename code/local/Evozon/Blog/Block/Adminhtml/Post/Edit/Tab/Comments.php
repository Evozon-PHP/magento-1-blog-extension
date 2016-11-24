<?php

/**
 * Show the post comments
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Comments extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * create form for Comments tab
     * 
     * @return Mage_Adminhtml_Block_Widget_Form
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareForm()
    {        
        // get module's helper for translations and other functions
        $_helper = Mage::helper('evozon_blog');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',            
            'action' => $this->getData('action'),
            'method'    => 'post'
        ));  
        
        $form->addFieldset('comments_fieldset', array('legend'=>$_helper->__('Comments')));
        
        $this->setForm($form);
    }        
    
}
