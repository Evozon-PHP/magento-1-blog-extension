<?php

/**
 * Actions for post grid tab in product edit form
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Shows the grid serializer with the blog posts related to the current product 
     * The layout is rendered when accessing Manage Products
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function postsAction()
    {
        $this->_initProductsGrid();
    }

    /**
     * Posts grid in the catalog page/edit product page
     * When selecting the grid from the Manage Products page, it will render it
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function postsGridAction()
    {
        $this->_initProductsGrid();
    }

    protected function _initProductsGrid()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('product.edit.tab.posts')
            ->setProductPosts($this->getRequest()->getPost('product_posts', null));

        $this->renderLayout();
    }
}
