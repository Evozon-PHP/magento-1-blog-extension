<?php

/**
 * Archive Week Condition Class
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Post_Archive_Condition_Year extends Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract
{
    /* CONST DATE_FORMAT = '%Y';
      CONST RESULT_LABEL = 'tt.label';
      CONST RESULT_URL = 'tt.url'; */

    /**
     * Constructor
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param int $website
     * @param $limit
     */
    public function __construct(Varien_Db_Adapter_Interface $adapter, $website, $limit)
    {
        $this->_limit = $limit;
        $this->_website = $website;

        parent::__construct($adapter, $website);
    }

    /**
     * @return string
     */
    protected function getWhere()
    {
        if($this->_limit > 0) {
            return 't.publish_date > DATE_SUB(now(), INTERVAL ' . $this->_limit . ' YEAR) AND t.publish_date < now()';
        }
        
        return 't.publish_date > MAKEDATE(YEAR(CURDATE()), 1) AND t.publish_date < now()';
    }

    /**
     * @return string
     */
    protected function getDateFormat()
    {
        return '%Y';
    }

    /**
     * @return string
     */
    protected function getResultLabel()
    {
        return 'tt.label';
    }

    /**
     * @return string
     */
    protected function getResultUrl()
    {
        return 'tt.url';
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
            ->where($this->getWhere())
            ->order('label DESC')
            ->group(array('label'));

        $select = $this->_adapter->select()
            ->from(
            array('tt' => $conditionalSelect), array(
            new Zend_Db_Expr("IF (tt.label>0, " . $this->getResultLabel() . ",'') as label"),
            'total' => 'tt.total',
            new Zend_Db_Expr("IF (tt.url>0, " . $this->getResultUrl() . ",'') as url")
            )
        );

        return $select;
    }

}
