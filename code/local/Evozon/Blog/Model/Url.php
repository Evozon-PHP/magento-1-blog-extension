<?php

/**
 * Url model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Url extends Mage_Core_Model_Abstract
{

    /**
     * Keeping url path
     * 
     * @var string
     */
    protected $_url;

    /**
     * array with the path for all author models
     * 
     * @var array
     */
    protected $_urlMap = array(
        'default' => 'evozon_blog/url_default',
        'archive' => 'evozon_blog/url_archive',
        'tag' => 'evozon_blog/url_tag',
        'post' => 'evozon_blog/url_post'
    );

    /**
     *
     * @var string | NULL 
     */
    protected $_model;

    /**
     * Constructor
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Return url model and function call
     * 
     * @return Evozon_Blog_Model_Url_Abstract
     */
    public function getUrl($model)
    {
        if (!$this->_url) {
            $model = (is_null($model)) ? 'default' : $model;
            $this->_model = $model;

            $url = Mage::getModel($this->getUrlClass($model))->setUrlPath();
            $this->_url = $url;
        }

        return $this->_url;
    }

    /**
     * Get the url class
     * 
     * @return string
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    protected function getUrlClass()
    {
        return $this->_urlMap[$this->_model];
    }

}
