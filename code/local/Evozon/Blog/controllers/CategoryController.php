<?php

/**
 * Description
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_CategoryController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $this->getLayout();
        $this->renderLayout();
    }
}
