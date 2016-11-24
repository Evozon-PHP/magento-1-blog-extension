<?php

/**
 * Archive Abstract Condition Class
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
abstract class Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract extends Varien_Object
{

    /**
     * \Varien_Db_Adapter_Interface
     */
    protected $_adapter;

    /**
     * Attributes for the post collection to be filtered by
     *
     * @var array
     */
    protected $_filterAttributes = array(
        'publish_date',
        'status',
        'store_visibility',
        'archive_status'
    );

    /**
     * Archive length
     *
     * @var int | null
     */
    protected $_limit = null;

    /**
     * Website id filter
     *
     * @var int
     */
    protected $_website;

    /**
     * Evozon Blog Post entity type used for selecting and joining with attributes and values
     *
     * @var int
     */
    protected $_entityType = Evozon_Blog_Model_Post::ENTITY;

    public function __construct(Varien_Db_Adapter_Interface $adapter, $website)
    {
        $this->_adapter = $adapter;
        $this->_website = $website;
    }

    /**
     * @return string
     */
    abstract protected function getDateFormat();

    /**
     * @return string
     */
    abstract protected function getResultLabel();

    /**
     * @return string
     */
    abstract protected function getResultUrl();

    /**
     * @return string
     */
    abstract protected function getWhere();

    /**
     * @return \Varien_Db_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @return array
     */
    public function getFilterAttributes()
    {
        return $this->_filterAttributes;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->_limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->_limit;
    }

    /**
     * @param int $website
     */
    public function setWebsite($website)
    {
        $this->_website = $website;
    }

    /**
     * @return int
     */
    public function getWebsite()
    {
        return $this->_website;
    }

    /**
     * We will inner and left join the entity with the requested attributes to be filtered by
     * (the filters are set in $_filterAttributes)
     * The archive will only count and keep in vision the posts that:
     * -> are on the current website
     * -> are on the selected store
     * -> are visibile on the store
     * -> have status published
     *
     * After getting the first $select, there will be another select on the previous one
     * to keep only the entity_id and a new field- publish_date, where it will be stored 
     * the specific store publish-date attribute value or the one from the default store (id = 0)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Zend_Db_Select
     */
    protected function getPostFilteredSelect()
    {
        //setting defaults
        $storeId = Mage::app()->getStore()->getStoreId();
        $select = $this->_adapter
            ->select()
            ->from(
            array('e' => 'evozon_blog_post_entity'), array('entity_id' => 'entity_id')
        );

        //joining each attribute with the table
        foreach ($this->getFilterAttributes() as $attrCode) {

            $attr = Mage::getModel('eav/config')->getAttribute($this->_entityType, $attrCode);
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackendTable();

            //table alias
            $alias = $this->_entityType . '_' . $attrCode;

            //setting conditions and constraints
            $innerCondition = array(
                $this->_adapter->quoteInto("{$attrCode}_default.entity_id = e.entity_id", ''),
                $this->_adapter->quoteInto("{$attrCode}_default.attribute_id = ?", $attrId),
                $this->_adapter->quoteInto("{$attrCode}_default.store_id = ?", 0)
            );
            $joinLeftConditions = array(
                $this->_adapter->quoteInto("{$alias}.entity_id = e.entity_id", ''),
                $this->_adapter->quoteInto("{$alias}.attribute_id = ?", $attrId),
                $this->_adapter->quoteInto("{$alias}.store_id = ?", $storeId),
            );

            $select
                ->joinInner(
                    array($attrCode . '_default' => $attrTable), implode(' AND ', $innerCondition),
                    array($attrCode . '_default' => 'value')
                )
                ->joinLeft(
                    array($alias => $attrTable), implode(' AND ', $joinLeftConditions),
                    array($attrCode => 'value', $attrCode . '_id' => 'value_id')
            );

            //setting visibility conditions
            if ($attrCode !== 'publish_date') {
                $select->where("IF ({$alias}.value_id > 0, {$alias}.value, {$attrCode}_default.value) = '1'");
            }
        }

        //filter by the website
        $websiteCondition = array(
            $this->_adapter->quoteInto("websites.post_id = e.entity_id", ''),
            $this->_adapter->quoteInto("websites.website_id = ?", $this->getWebsite()),
        );

        $select->joinInner(
            array('websites' => Mage::getSingleton('core/resource')->getTableName('evozon_blog/post_website')),
            implode(' AND ', $websiteCondition)
        );

        $publishDateSelect = $this->_adapter->select()
            ->from(
            array('joins' => $select),
            array(
                'entity_id' => 'joins.entity_id',
                new Zend_Db_Expr("IF (joins.publish_date_id > 0 ,joins.publish_date, joins.publish_date_default) AS 'publish_date'")
            )
        );

        return $publishDateSelect;
    }

    /**
     * @return mixed
     */
    public function getSelect()
    {
        $conditionalSelect = $this->_adapter->select()
            ->from(array('t' => $this->getPostFilteredSelect()));

        $conditionalSelect
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '" . $this->getDateFormat() . "') as label"))
            ->columns(new Zend_Db_Expr("count(`t`.`entity_id`) as total"))
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '" . $this->getDateFormat() . "') as url"))
            ->where($this->getWhere());

        $select = $this->_adapter->select()
            ->from(
            array('tt' => $conditionalSelect),
            array(
                new Zend_Db_Expr("IF (tt.label>0, " . $this->getResultLabel() . ",'') as label"),
                'total' => 'tt.total',
                new Zend_Db_Expr("IF (tt.url>0, " . $this->getResultUrl() . ",'') as url")
            )
        );

        return $select;
    }

}
