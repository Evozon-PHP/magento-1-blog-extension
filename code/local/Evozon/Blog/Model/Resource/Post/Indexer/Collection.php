<?php
/**
 * Indexer collection with url_rewrites
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Indexer_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Define main table and
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->getMainTableName());
    }

    /**
     * Setting main table name depending on the magento edition it is used
     * @return string
     */
    public function getMainTableName()
    {
        return (string) Mage::getSingleton('evozon_blog/factory')->getRewriteMainTable();
    }

    /**
     * Loads existing rewrites with special fields to make it easier to merge in the prepared rewrites collection
     * that will be saved in further steps
     *
     * @see Evozon_Blog_Model_Indexer_UrlRewrite_Data_Rewrites
     * @see Evozon_Blog_Model_Indexer_UrlRewrite_RewriteGenerator
     * @return $this
     */
    public function loadExistingRewrites()
    {
        $pattern = Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE;
        $pathLength = strlen($pattern)+1;
        $this->addFieldToSelect(array('target_path','store_id'))
            ->addExpressionFieldToSelect('entity_id',"SUBSTR({{target_path}}, $pathLength)", 'target_path')
            ->addExpressionFieldToSelect('id',"CONCAT(SUBSTR({{target_path}}, $pathLength),'-', {{store_id}})", array('target_path'=>'target_path','store_id'=>'store_id'))
            ->addFieldToFilter('target_path', array('like'=> $pattern . '%'));

        return $this;
    }

    /**
     * Setting store filter
     *
     * @param array | null $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        if (!empty($storeId)) {
            $this->addFieldToFilter('store_id', array('in' => $storeId));
        }

        return $this;
    }

    /**
     * Setting ids filter
     * @param array | null $ids
     * @return $this
     */
    public function addEntityIdFilter($ids)
    {
        if (!empty($ids)) {
            array_walk($ids, function (&$id) {
                $id = Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE . $id;
            });
            $this->addFieldToFilter('target_path', array('in' => $ids));
        }


        return $this;
    }

    /**
     * Setting resource model name
     * @solved issues for enterprise 1.12
     * 
     * @return string
     */
    public function getResourceModelName()
    {
        return Evozon_Blog_Model_Factory::EVOZON_BLOG_URL_INDEXER;
    }
}