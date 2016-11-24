<?php

/**
 * Search Controller
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
require_once 'Mage/CatalogSearch/controllers/ResultController.php';
class Evozon_Blog_ResultController extends Mage_CatalogSearch_ResultController
{

    /**
     * Used to display the blog posts results from search
     */
    public function blogAction()
    {
        $this->indexAction();
    }

}
