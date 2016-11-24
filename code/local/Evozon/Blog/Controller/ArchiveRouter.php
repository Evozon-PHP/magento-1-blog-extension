<?php

/**
 * Router for archive links
 * 
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Controller_ArchiveRouter extends Evozon_Blog_Controller_AbstractRouter
{
    /**
     * Constant for controller
     */
    const EVOZON_BLOG_ROUTER_CONTROLLER = 'archive';

    /**
     * constant for action
     */
    const EVOZON_BLOG_ROUTER_ACTION = 'view';

    /**
     * Will match archive path
     * ex: {archive_segment}/week or {archive_segment}/year/month
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param  int $id | null
     * @return boolean
     */
    protected function matchPath()
    {
        // set defaults
        $path = implode('/', $this->_requestPath);

        $archiveSegment = $this->getConfigModel()->getPostArchiveConfig(Evozon_Blog_Model_Config_Post::ARCHIVE_URL_SEGMENT);
        $regExp = '/(' . $archiveSegment . ')\/' .
            '('. Mage::helper('evozon_blog')->__('week') . '||'
            . '(\d{4}\/([0][1-9]||[1][0-2]))'
            . ')$'
            . '/';

        if (!preg_match($regExp, $path)) {
            return false;
        }

        return true;
    }

    /**
     * Return the params that has to be set on the request.
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getParams()
    {
        $params = array();
        
        $params['period'] = $this->_requestPath[1];
        //if we have a format of month/year
        if (count($this->_requestPath) == 3) {
            $params['month'] = $this->_requestPath[2];
        }

        return $params;
    }
}
