<?php

/**
 * Router for tag links
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Controller_TagRouter extends Evozon_Blog_Controller_AbstractRouter
{
    /**
     * Constant for controller
     */    
    const EVOZON_BLOG_ROUTER_CONTROLLER = 'tag';
    
    /**
     * Constant for action
     */
    const EVOZON_BLOG_ROUTER_ACTION = 'view';
    
    /**
     * Match the router path against the logic the tag url are being generated
     * 
     * @param int $id | null
     * @return boolean
     */
    protected function matchPath()
    {
        $path = $this->_requestPath;
        if (count($path) < 2) {
            return false;
        }

        $tagSegment = $this->getConfigModel()->getPostTagsConfig(Evozon_Blog_Model_Config_Post::TAGS_BLOCK_URL_SEGMENT);
        if (empty($path[0]) || $path[0] != $tagSegment) {
            return false;
        }               
        
        return true;
    }
    
     /**
     * Return the params that has to be set on the request
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     * @return array
     */
    protected function getParams()
    {       
        $params = array();
        
        $params['url_key'] = $this->_requestPath[1];
        $params['segment'] = $this->_requestPath[0];

        return $params;              
    }
}
