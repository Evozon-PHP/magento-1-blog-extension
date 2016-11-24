<?php

/**
 * Rss Controller
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */

require_once 'Mage/Rss/controllers/CatalogController.php';
class Evozon_Blog_RssController extends Mage_Rss_CatalogController
{
    /**
     * Recent posts RSS feed
     * 
     * @author Calin Florea <calin.florea@evozon.com>
     */
    public function indexAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        
        $this->loadLayout(false);
        $this->renderLayout();
    }
}
