<?php

/**
 * System controller displays helping messages to user on how to configure the blog post
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Blog_SystemController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Displaying the popup
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function helperAction(){
        //setting the posts for the grid & rendering the layout
       $this->loadLayout();
       $this->renderLayout();   
    }
}