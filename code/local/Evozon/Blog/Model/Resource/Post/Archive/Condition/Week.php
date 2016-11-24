<?php

/**
 * Archive Week Condition Class
 *
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Lilian Codreanu <lilian.codreanu@evozon.com>
 */
class Evozon_Blog_Model_Resource_Post_Archive_Condition_Week extends Evozon_Blog_Model_Resource_Post_Archive_Condition_Abstract
{

    /**
     * @return string
     */
    protected function getDateFormat()
    {
        return '%u';
    }

    /**
     * @return string
     */
    protected function getWhere()
    {
        return 't.publish_date > DATE_SUB(now(), INTERVAL 1 WEEK)';
    }

    /**
     * @return string
     */
    protected function getResultLabel()
    {
        return '"This week"';
    }

    /**
     * @return string
     */
    protected function getResultUrl()
    {
        return '"week"';
    }


}