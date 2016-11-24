<?php

/**
 * Evozon Blog Indexer Url class
 *
 * @package     Evozon_Blog
 * @author      Szegedi Szilard <szilard.szegedi@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Adminhtml_System_Config_Backend_Search_Engine extends Mage_Core_Model_Config_Data
{
    /**
     * After save call
     * Invalidate blog search index if engine was changed
     *
     * @return Evozon_Blog_Model_Adminhtml_System_Config_Backend_Search_Engine
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        if ($this->isValueChanged()) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }

        return $this;
    }
}
