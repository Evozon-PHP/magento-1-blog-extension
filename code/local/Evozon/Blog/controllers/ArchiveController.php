<?php

/**
 * Archive controller
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
require_once 'Evozon/Blog/controllers/LayoutController.php';
class Evozon_Blog_ArchiveController extends Evozon_Blog_LayoutController
{

    /**
     * defaults to store used data and request params
     * they are set in the _initAction()
     * you can add your own periods if they are first set in Evozon_Blog_Model_Resource_Post_Archive for the collection to be filtered by
     * (see the $_archiveConditions variable)
     * 
     * @var array | int | NULL
     */
    protected $_dataVersions = NULL;
    
    /**
     *
     * @var string 
     */
    protected $_period;
    
    /**
     *
     * @var string 
     */
    protected $_month;

    /**
     * Initializing defaults
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_ArchiveController
     */
    protected function _initAction()
    {
        Mage::register('is_blog', true);

        $this->_dataVersions = array(
            Mage::helper('evozon_blog')->__('week') => 'week',
        );

        $this->_period = $this->getRequest()->getParam('period');
        $this->_month = $this->getRequest()->getParam('month');

        return $this;
    }

    /**
     * Getting date periods that will be passed to Evozon_Blog_Block_Post_List to filter posts collection
     * Before loading the view, we have to set the desired layout selected for the default layout in configurations
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_ArchiveController
     */
    public function viewAction()
    {
        $this->_initAction();
        $this->getBlogLayout();

        $dates = $this->getDatePeriods();

        /**
         *  set flag for archive
         *  @see Evozon_Blog_Block_Post_List
         */
        $this->getLayout()
            ->getBlock('post_list')
            ->setListType('archive')
            ->setFromDate($dates[0])
            ->setToDate($dates[1]);

        $this->renderLayout();
    }

    /**
     * From received parameters, the fromDate and toDate will be calculated
     * based on the period received
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    protected function getDatePeriods()
    {
        $today = now();

        if (!is_numeric($this->_period)) {
            $fromDate = date('Y-m-d H:i:s', strtotime('-1 week', strtotime($today)));
            $toDate = $today;
        } else {
            $fromDate = $this->_period . '-' . $this->_month . '-01 00:00:00';
            $toDate = $this->_period . '-' . $this->_month . '-31 23:59:59';
        }

        return array($fromDate, $toDate);
    }

}
