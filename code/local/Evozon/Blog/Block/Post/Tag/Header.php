<?php

/**
 * Set header template for posts list when using the tag filter
 * 
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Tag_Header extends Evozon_Blog_Block_Post_List
{
    /**
     * If template not set, do this
     */
    public function getTemplate()
    {
        if (empty($this->_template))
        {
            return 'evozon/blog/post/tag/header.phtml';
        }

        return $this->_template;
    }

    /**
     * Setting breadcrumbs
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {

            $breadcrumbsBlock->addCrumb('home', array(
                'label' => Mage::helper('evozon_blog')->__('Home'),
                'title' => Mage::helper('evozon_blog')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ));
            $breadcrumbsBlock->addCrumb('tag', array(
                'label' => $this->getHeaderTitle(),
                'title' => $this->getHeaderTitle()
            ));
        }

        parent::_prepareLayout();
    }

    /**
     * Return tag name by received url_key
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function getName()
    {
        $urlKey = $this->getRequest()->getParam('url_key');

        if (!empty($urlKey)) {
            try {
                $tag = Mage::getModel('evozon_blog/tag')->getResourceCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToFilter('url_key', array('eq' => $urlKey))
                    ->getFirstItem();
                return $tag->getName();
            } catch (Exception $exc) {
                Mage::logException($exc);
            }
        }
    }

    /**
     * Setting page header while on filtering by tag action
     * 
     * @return string
     */
    protected function getHeaderDefault()
    {
        return $this->getConfigModel()->getPostTagsConfig(Evozon_Blog_Model_Config_Post::TAGS_BLOCK_HEADER);
    }
}
