<?php

/**
 * Posts archive months block type
 * 
 * @package     Evozon_Blog 
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Archive_Block_View extends Mage_Core_Block_Template
{

    protected function _construct()
    {
        parent::_construct();

        if (!$this->getTemplate()) {
            $this->setTemplate('evozon/blog/post/archive/view.phtml');
        }
    }
}
