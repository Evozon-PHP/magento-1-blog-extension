<?php

/**
 * Actions for post grid tab in category edit form
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_Action
{

    public function loadgridAction()
    {
        $this->_initCategoryGrid();
    }

    public function postsAction()
    {
        $this->_initCategoryGrid();
    }

    protected function _initCategoryGrid()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('category.edit.tab.post')
            ->setCategoryPosts($this->getRequest()->getPost('post_category', null));
        $this->renderLayout();
    }
}
