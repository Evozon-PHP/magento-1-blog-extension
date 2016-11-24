<?php

/**
 * Load the collection of blog posts so that blog posts can be assigned to certain products.
 *
 * @package    Evozon_Blog
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Catalog_Product_Edit_Tab_Post extends Evozon_Blog_Block_Adminhtml_Catalog_Edit_Tab_AbstractPost
{       

    /**
     * Retrieve selected blog posts
     * the selected data can be retrieved by calling the getProductPosts() method
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array 
     */
    protected function _getSelectedPosts()
    {
        $posts = $this->getProductPosts();

        if (!is_array($posts)) {
            //if we have no data to take by ajax, we return the array with the already selected posts
            //that are saved in the database
            $posts = $this->getSelectedPosts();
        }

        return $posts;
    }

    /**
     * Retrieve selected posts 
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @access protected
     * @return array
     * 
     */
    public function getSelectedPosts()
    {
         $selected = Mage::getResourceSingleton('evozon_blog/catalog_product')
            ->getPostIds($this->_getEntityId());
        
        return $selected;
    }    

    /**
     * Rerieve grid URL by ajax
     * the grid that displays our blog posts on reset/search/etc
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @access public
     * @return string
     * 
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/catalog_product/postsGrid', array(
                'id' => $this->_getEntityId(), '_current' => true
        ));
    }

}
