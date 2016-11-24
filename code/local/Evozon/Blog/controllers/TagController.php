<?php

/**
 * Tag controller
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
require_once 'Evozon/Blog/controllers/LayoutController.php';
class Evozon_Blog_TagController extends Evozon_Blog_LayoutController
{

    /**
     * Maintains the url_key
     * @var string 
     */
    protected $_urlKey = null;

    /**
     * Maintains the tag segment
     * @var string
     */
    protected $_segment = null;

    /**
     * Initializing defaults
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_TagController
     */
    protected function _initAction()
    {
        Mage::register('is_blog', true);

        $this->_urlKey = $this->getRequest()->getParam('url_key');
        $this->_segment = $this->getRequest()->getParam('segment');

        return $this;
    }

    /**
     * Getting tag by which the posts collection will be filtered
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_TagController
     */
    public function viewAction()
    {
        $this->_initAction();
        $this->_checkIfStoreHasBeenChanged();
        $this->getBlogLayout();

       $tag = Mage::getModel('evozon_blog/tag')->getResourceCollection()
            ->addAttributeToFilter('url_key', array('eq' => $this->_urlKey))
            ->getFirstItem();

        /**
         *  set flag for archive
         *  @see Evozon_Blog_Block_Post_List
         */
        $this->getLayout()
            ->getBlock('post_list')
            ->setListType('tag')
            ->setTag($tag);

        $this->renderLayout();
    }

    /**
     * Acts like a route-rewrite
     * In case the store has been changed while being on a tag,
     * The url has to change and the action has to be reloaded with the propper url_key
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function _checkIfStoreHasBeenChanged()
    {
        $tag = Mage::getResourceModel('evozon_blog/tag')
            ->getEntityIdByUrlKey($this->_urlKey, (int) Mage::app()->getStore()->getStoreId());

        $urlKey = $tag['store'] === null ? $tag['default'] : $tag['store'];
        if ($urlKey != $this->_urlKey) {
            $this->_redirectUrl(Mage::getBaseUrl() . $this->_segment . '/' . $urlKey);
        }

        return $this;
    }

}
