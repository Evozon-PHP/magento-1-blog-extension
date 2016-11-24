<?php

/**
 * Blog Post view
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_View extends Evozon_Blog_Block_Post_Abstract
{
    /**
     * built-in constructor. Set the template, if not already set.
     */
    public function __construct()
    {
        parent::__construct();

        $template = $this->getTemplate();
        if (empty($template)) {
            $this->setTemplate('evozon/blog/post/view.phtml');
        }
    }

    /**
     * while preparing layout, get breadcrumbs block and add category paths and current post link
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return \Evozon_Blog_Block_Post_View
     */
    protected function _prepareLayout()
    {
        $this->_addBreadcrumbsToLayout()
            ->_addMetaDataTagsToLayout();

        parent::_prepareLayout();

        return $this;
    }

    /**
     * Add Breadcrumbs To Layout
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return $this
     */
    protected function _addBreadcrumbsToLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $post = $this->getPost();

            $breadcrumbsBlock->addCrumb('home', array(
                'label' => Mage::helper('evozon_blog')->__('Home'),
                'title' => Mage::helper('evozon_blog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));

            $category = $this->_getCategoryForBreadcrumbs();
            if (!empty($category)) {
                $path = Mage::helper('catalog')->getBreadcrumbPath();
                $path['category' . $category->getId()]['link'] = $category->getUrl();

                foreach ($path as $name => $breadcrumb) {
                    $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                }
            }

            // add current post title to breadcrumb
            $breadcrumbsBlock->addCrumb('post', array(
                'label' => $post->getTitle(),
                'title' => $post->getTitle(),
                'link' => $post->getPostUrl()
            ));
        }

        return $this;
    }

    /**
     * In case the user accesses a post randomly (outside the website/a non-blog category/etc,
     * the breadcrumb leading towards the article should include one of the original categories it appears on
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _getCategoryForBreadcrumbs()
    {
        $categories = $this->getPost()->getCategoryIds();
        $lastVisitedCategoryId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();

        if (in_array($lastVisitedCategoryId, $categories) || empty($categories))
        {
            $category = Mage::getModel('catalog/category')->load($lastVisitedCategoryId);
        } else {
            $category = Mage::getSingleton('catalog/category')->getResourceCollection()
                ->addAttributeToFilter('entity_id', array('in'=>$categories))
                ->setOrder('level', 'DESC')
                ->getFirstItem();
        }

        Mage::register('current_category', $category);
        return $category;
    }

    /**
     * Set title tag from meta or post title
     * Set keywords from meta or post title
     * Set description from post meta description or from post description.
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return $this
     */
    protected function _addMetaDataTagsToLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {

            $post = $this->getPost();

            if (($title = $post->getMetaTitle()) || ($title = $post->getTitle())) {
                $headBlock->setTitle($title);
            }

            if (($keywords = $post->getMetaKeywords()) || ($keywords = $post->getTitle())) {
                $headBlock->setKeywords($keywords);
            }

            if (($description = $post->getMetaDescription()) || ($description = Mage::helper('core/string')->substr($post->getDescription(), 0, 255))) {
                $headBlock->setDescription($description);
            }
        }

        return $this;
    }

    /**
     * Get if to display the related products to current blog post
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return bool
     */
    public function getShowRelatedProducts()
    {
        return (bool) $this->getConfigModel()->getPostProductsConfig(Evozon_Blog_Model_Config_Post::RELATED_PRODUCTS_POST_PAGE_ENABLED);
    }
}
