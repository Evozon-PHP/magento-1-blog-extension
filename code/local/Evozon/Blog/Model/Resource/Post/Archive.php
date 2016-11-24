<?php

/**
 * Archive deals with the groups of data fetched to the archive block on the blog category or post view page
 * It can easily be extended by adding other time period counts and filters
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Post_Archive extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * contains the data array to be send to template
     *
     * @var array | null
     */
    protected $_archiveData = null;

    /**
     * read connection to the resource
     *
     * @var \Varien_Db_Adapter_Interface
     */
    protected $_adapter;

    /**
     * Conditions for creating the period selects to be used in union
     * The key contains the format for DATE_FORMAT
     * The value contains the where restriction
     *
     * @var array
     */
    protected $_archiveConditions = array();

    /**
     * Evozon Blog Post entity type used for selecting and joining with attributes and values
     *
     * @var int
     */
    protected $_entityType = Evozon_Blog_Model_Post::ENTITY;

    /**
     * The model does not store the data in a table
     * and it doesn`t get the values from one,
     * so the read connection is set manualy via core/resource model
     */
    protected function _construct()
    {
        $this->_adapter = Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * It is called from Evozon_Blog_Block_Post_Archive_Block to get data on archive selections
     *
     * @param int $website
     * @param null $limit
     * @return array|null
     */
    public function getArchiveCollection($website, $limit = null)
    {
        if ($this->_archiveData === null) {

            $this->_addArchiveConditions(
                new Evozon_Blog_Model_Resource_Post_Archive_Condition_Week($this->getAdapter(), $website)
            );

            $this->_addArchiveConditions(
                    new Evozon_Blog_Model_Resource_Post_Archive_Condition_Year($this->getAdapter(), $website, $limit)
            );

            $select = $this->_adapter->select()
                ->union(($this->getArchiveSelects()), Zend_Db_Select::SQL_UNION_ALL);

            $this->_archiveData = $this->_adapter->fetchAll($select);
        }

        return $this->_archiveData;
    }

    /**
     * @param Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract $condition
     * @return $this
     */
    protected function _addArchiveConditions(Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract $condition)
    {
        $this->_archiveConditions[] = $condition;
        return $this;
    }

    /**
     * @return array
     */
    protected function _getArchiveConditions()
    {
        return $this->_archiveConditions;
    }

    /**
     * It will filter the posts published in specific year
     * and will group them by months
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $website
     * @return array
     */
    public function getMonthlyArchiveCollection($website)
    {
        $monthCondition = new Evozon_Blog_Model_Resource_Post_Archive_Condition_Month($this->_adapter, $website);
        return $this->_adapter->fetchAll($monthCondition->getSelect());
    }

    /**
     * Creating 3 individual selects queries (week, month, year) to be used in the getArchiveCollection()
     * From received select with entity_id and publish_date field, the output will be label total and url
     * Label contains the period label (taken from $this->_archiveConditions)
     * Total contains the number of entity_ids that fit in the where condition (based on the period)
     * Url contains the url segment designated to the period (taken fron $this->_archiveConditions)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Zend_Db_Select
     */
    protected function getArchiveSelects()
    {
        $selects = array();

        foreach ($this->_getArchiveConditions() as $condition) {
            $selects[] = $condition->getSelect();
        }

        return $selects;

    }

    /**
     * @return \Varien_Db_Adapter_Interface
     */
    protected function getAdapter()
    {
        return $this->_adapter;
    }
}