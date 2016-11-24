<?php

/**
 * Set header template for posts list when using the archive feature
 * 
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Archive_Header extends Evozon_Blog_Block_Post_List
{

    protected $_period;

    /**
     * If template not set, do this
     */
    public function getTemplate()
    {
        if (empty($this->_template))
        {
            return 'evozon/blog/post/archive/header.phtml';
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

            $breadcrumbsBlock->addCrumb('archive', array(
                'label' => $this->getHeaderTitle(),
                'title' => $this->getHeaderTitle()
            ));
        }

        parent::_prepareLayout();
    }

    /**
     * Return formated period
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function getName()
    {
        $this->_period = $this->getRequest()->getParam('period');

        if (is_numeric($this->_period)) {
            return date('F', mktime(0, 0, 0, $this->getRequest()->getParam('month'))) . ', ' . $this->_period;
        }

        return Mage::helper('evozon_blog')->__('This ') . $this->_period;
    }

    /**
     * Setting page header while on filtering by tag action
     * 
     * @return string
     */
    protected function getHeaderDefault()
    {
        return $this->getConfigModel()->getPostArchiveConfig(Evozon_Blog_Model_Config_Post::ARCHIVE_HEADER);
    }

}
