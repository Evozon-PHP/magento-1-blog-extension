<?php

/**
 * Resource Model for Schedule data Model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Resource_Scheduler extends Mage_Core_Model_Resource_Db_Abstract
{
    const EVOZON_BLOG_SCHEDULE_DATA_TABLE = 'evozon_blog/scheduler';
    
    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/scheduler', 'id');
    }
    
    /**
     * Delete all entries which has been changed
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $postData Contains informations about post id and store id
     */
    public function deleteByPostAndStoreIds(array $postData)
    {
        if (empty($postData)) {
            return $this;
        }
        
        $writeAdapter = $this->_getWriteAdapter();
        
        $where = array();
        foreach ($postData as $value) {
            $where[] = $writeAdapter->quoteInto("(store_id = ?", (int) $value['store_id']) . ' AND ' . 
                $writeAdapter->quoteInto("post_id = ?)", (int) $value['post_id']
            );
        }
        
        $writeAdapter->delete($this->getMainTable(), new Zend_Db_Expr(implode(' OR ', $where)));
    }
}
