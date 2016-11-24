<?php
/**
 * Evozon Blog Indexer resource
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Resource_Post_Indexer extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init($this->getMainTableName(), 'url_rewrite_id');
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
     * Getting fields that require update on each save
     * @return array
     */
    public function getFieldsToUpdate()
    {
        return Mage::getSingleton('evozon_blog/factory')->getFieldsToUpdate();
    }

    /**
     * Initialize unique array fields
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(
            array('field' => array('request_path', 'store_id', 'target_path'),
                'title' => __('Request Post Url Path for Specific Store')
            ),
        );

        return $this;
    }

    /**
     * Loading request path based on the target path and store id
     * It is also applicable for the EE version
     *
     * @param $path
     * @param $store
     * @return array
     */
    public function loadRequestPathByTargetPath($path, $store)
    {
        $adapter = $this->getReadConnection();
        $select= $adapter->select()
            ->from($this->getMainTable(), 'request_path')
            ->where('target_path = ?', $path)
            ->where('store_id = ?', (int) $store)
            ->limit(1);

        return $adapter->fetchOne($select);
    }

    /**
     * Insert multiple rewrites
     *
     * @param $rewrites Varien_Data_Collection
     * @return int
     */
    public function saveRewrites($rewrites)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $result = $writeAdapter->insertOnDuplicate(
            $this->getMainTable(),
            $rewrites,
            $this->getFieldsToUpdate()
        );

        return $result;
    }

    /**
     * Delete all rewrites
     * Used when applying a full reindexing of the blog post urls
     *
     * @return $this
     */
    public function deleteFullRewrites()
    {
        $where = $this->getReadConnection()->quoteInto("target_path LIKE ?",
            Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE . '%'
        );
        $this->_delete($where);
        return $this;
    }

    /**
     * Delete existing rewrites
     * If there is a store id set - then the rewrites for given post ids will be deleted
     *
     * @param array $postIds
     * @param int | null $storeId
     * @return $this
     */
    public function deleteRewrites(array $postIds, $storeId = null)
    {
        $adapter = $this->getReadConnection();
        $conditions = array();

        if ($storeId)
        {
            $conditions[] = $adapter->quoteInto("store_id LIKE ?", $storeId);
        }

        foreach ($postIds as $postId) {
            $conditionByPost[] = $adapter->quoteInto("target_path = ?",
                Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE . $postId
            );
        }

        $where = '( '. implode(' OR ', $conditionByPost) . ' )';
        if ($storeId)
        {
            $conditions = array(
                $where,
                $adapter->quoteInto("store_id = ?", $storeId)
            );
            $where = implode(' AND ', $conditions);
        }

        $this->_delete($where);
        return $this;
    }

    /**
     * Deleting rewrites
     *
     * @param string $where
     * @return $this
     * @throws Exception
     */
    protected function _delete($where)
    {
        $this->beginTransaction();
        try {
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Creates a select sql used to attach to the _afterLoad of Evozon_Blog_Model_Resource_Post_Collection
     * in order to have the request_path for each post entity on collection load
     *
     * @param int $storeId
     * @return Varien_Db_Select
     */
    public function getSelectQueryForPostsCollection($storeId)
    {
        $adapter = $this->_getReadAdapter();
        $expr = $adapter->quoteInto('SUBSTR(target_path, LENGTH(?)+1) AS entity_id', Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE);

        $select = $adapter
            ->select()
            ->from($this->getMainTable(),
                array('request_path', 'store_id', new Zend_Db_Expr($expr)))
            ->where("target_path LIKE ?", Evozon_Blog_Model_Indexer_UrlRewrite_PathGenerator::EVOZON_URL_TARGET_PATH_PATTERN_LIKE.'%')
            ->where('store_id = ?', $storeId);

        return $select;
    }

    /**
     * Getting all request_paths from the db
     * Used to validate the uniqueness of the created path
     * In the db the request paths are already saved to be unique, so it will be used as the key to the fetched result
     *
     * @return array
     */
    public function getExistingDbRewrites()
    {
        $adapter = $this->_getReadAdapter();
        $select= $adapter
            ->select()
            ->from(
            $this->getMainTable(),
            array(
                'path' => new Zend_Db_Expr('CONCAT(request_path, "##", store_id)'),
                'target_path'
            )
        );

        return $adapter->fetchPairs($select);
    }
}