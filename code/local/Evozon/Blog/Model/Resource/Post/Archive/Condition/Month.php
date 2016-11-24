<?php

/**
 * Archive Month Condition Class
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Post_Archive_Condition_Month extends Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract
{


    /**
     * @return string
     */
    protected function getDateFormat(){
        return '';
    }

    /**
     * @return string
     */
    protected function getResultLabel(){
        return '';
    }

    /**
     * @return string
     */
    protected function getResultUrl(){
        return '';
    }

    /**
     * @return string
     */
    protected function getWhere(){
        return '';
    }

    /*
     *
     */
    public function getSelect()
    {
        $filterSelect = $this->getPostFilteredSelect();

        $select = $this->_adapter->select()
            ->from(array('t' => $filterSelect));

        $select->reset(Zend_Db_Select::COLUMNS)
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '%m') as month"))
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '%Y') as year"))
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '%M') as label"))
            ->columns(new Zend_Db_Expr("count(`t`.`entity_id`) as total"))
            ->columns(new Zend_Db_Expr("DATE_FORMAT(`t`.`publish_date`, '%Y/%m') as url"))
            ->group(array('month', 'year'))
            ->order(array('t.publish_date ASC'));

        return $select;
    }
}