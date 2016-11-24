<?php

/**
 * Posts archive block
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_Archive_Block extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * Will keep archive rows fetched from db
     *
     * @var Evozon_Blog_Model_Resource_Post_Archive
     */
    protected $archiveCollection = null;

    /**
     * Will keep months archive rows fetched from db
     *
     * @var Evozon_Blog_Model_Resource_Post_Archive
     */
    protected $monthlyCollection = null;

    /**
     * Url segment taken from config
     *
     * @var string
     */
    protected $urlSegment;

    /**
     * Config option if to show the number of posts next to archive period or not
     *
     * @var bool
     */
    protected $showCount;

    /**
     * Set helper and blog category, used to display widget
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();

        $this->showCount = $this->getConfigData('show_count');
        $this->setIsBlogCategory();
    }

    /**
     * Creates basic archive view by week, month and all years according to the limit that`s set in configurations
     * If there are no posts yet, or there is no posts for one of the periods, it won`t be shown
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getArchiveBlock()
    {
        $archiveCollection = array();

        if (!$this->isEmptyArchiveCollection()) {
            $archiveCollection = $this->getArchiveCollection();
            $this->getMonthlyCollection();

            for ($i = 0; $i <= count($archiveCollection) - 1; $i++) {
                $archiveCollection[$i]['url'] = Mage::getBaseUrl() . $this->getUrlSegment() .
                    '/' . $archiveCollection[$i]['url'];

                // if show count is set to 1
                if ($this->showCount && $archiveCollection[$i]['label']) {
                    $archiveCollection[$i]['label'] = $archiveCollection[$i]['label'] .
                        ' (' . $archiveCollection[$i]['total'] . ')';
                }
            }
        }

        return $archiveCollection;
    }

    /**
     * Check if Archive Collection is empty
     *
     * @return bool
     */
    protected function isEmptyArchiveCollection()
    {
        $archiveCollection = $this->getArchiveCollection();
        if (count($archiveCollection) == 1 && $archiveCollection[0]['total'] == 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns archive collection with posts that can be shown.
     *
     * @author  Dana Negrescu <dana.negrescu@evozon.com>
     * @return  Evozon_Blog_Model_Resource_Post_Archive
     */
    protected function getArchiveCollection()
    {
        if (!$this->archiveCollection) {
            // set defaults
            $limit = $this->getConfigData(Evozon_Blog_Model_Config_Post::ARCHIVE_HISTORY_LENGTH);
            $website = (int) Mage::app()->getWebsite()->getId();

            $this->archiveCollection = Mage::getResourceModel('evozon_blog/post_archive')
                ->getArchiveCollection($website, $limit);
        }

        return $this->archiveCollection;
    }

    /**
     * Returns archive collection with posts that are grouped according each month
     *
     * @author  Dana Negrescu <dana.negrescu@evozon.com>
     * @return  Evozon_Blog_Model_Resource_Post_Archive
     */
    protected function getMonthlyCollection()
    {
        if (!$this->monthlyCollection) {

            $processedArray = array();
            $monthlyCollection = Mage::getResourceModel('evozon_blog/post_archive')
                ->getMonthlyArchiveCollection((int) Mage::app()->getWebsite()->getId());

            foreach ($monthlyCollection as $data) {
                $data['url'] = Mage::getBaseUrl() . $this->getUrlSegment() . '/' . $data['url'];

                if ($this->showCount) {
                    $data['label'] = $data['label'] . ' (' . $data['total'] . ')';
                }

                $processedArray[$data['year']][] = array('label' => $data['label'], 'total' => $data['total'], 'url' => $data['url']);
            }
            $this->monthlyCollection = $processedArray;
        }

        return $this->monthlyCollection;
    }

    /**
     * Returns the labels and names for showing months with their posts count
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $year
     * @return array
     */
    public function getMonthsCollection($year)
    {
        return $this->monthlyCollection[$year];
    }

    /**
     * Get config data
     *
     * @return  string | int
     */
    protected function getConfigData($config)
    {
        return $this->getConfigModel()->getPostArchiveConfig($config);
    }

    /**
     * @return string
     */
    public function getUrlSegment()
    {
        if (!$this->urlSegment) {
            $this->urlSegment = $this->getConfigData(Evozon_Blog_Model_Config_Post::ARCHIVE_URL_SEGMENT);
        }

        return $this->urlSegment;
    }
}
