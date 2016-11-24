<?php

/**
 * Abstract class for post-additional elements relations resources
 * Additional elements, at the release time are:
 * 1. websites
 * 2. categories
 * 3. products
 * 4. tags
 * 5. other posts
 * 
 * The resource functions are the same, just specific parameters have to be set
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
abstract class Evozon_Blog_Model_Resource_Post_Relations_Abstract extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * post field name in the table by which the evozon_blog_post entity will be identified
     */
    const EVOZON_BLOG_POST_FIELD = "post_id";

    /**
     * id fieldname for the resource 
     */
    const EVOZON_BLOG_ID_FIELDNAME = "rel_id";

    /**
     * table name used to maintain the relations
     * @var string
     */
    protected $_table = null;
    
    /**
     * @var int postId used to find/delete/add/etc relations
     */
    protected $_postId;

    /**
     * Initialize resource: set main table and identifier
     */
    public function __construct()
    {
        $this->_init($this->getResourceName(), $this->getResourceFieldName());
        parent::__construct();
    }
    
    /**
     * Defining table and other parameters
     */
    protected function _construct()
    {
        $this->_table = $this->getTable($this->getTableName());
    }

    /**
     * @return string
     */
    abstract protected function getField();

    /**
     * @return string
     */
    abstract protected function getTableName();

    /**
     * @return string resource name
     */
    abstract protected function getResourceName();

    /**
     * Resource ID fieldname
     * 
     * @return string
     */
    protected function getResourceFieldName()
    {
        return self::EVOZON_BLOG_ID_FIELDNAME;
    }

    /**
     * Resource post field name
     * 
     * @return string
     */
    protected function getPostField()
    {
        return self::EVOZON_BLOG_POST_FIELD;
    }
    
    /**
     * Id by which the resource actions will be done
     * 
     * @param int $postId
     * @return \Evozon_Blog_Model_Resource_Post_Relations_Abstract
     */
    public function setPostId($postId)
    {
        $this->_postId = $postId;
        return $this;
    }
    
    /**
     * Get post id
     * @return int
     */
    public function getPostId()
    {
        return $this->_postId;
    }

    /**
     * Getting all relations post - object from the required table
     * 
     * @param int $id
     * @return array
     */
    public function getIdsByPostId($id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_table, $this->getField())
            ->where($this->getPostField() . ' = ?', (int) $id);

        return $adapter->fetchCol($select);
    }
    
    /**
     * Check if exist relation between a post and an additional object (website, product, etc)
     * 
     * @param int $postId
     * @param int $id
     * @return bool
     */
    public function hasRelationToPost($postId, $id)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter->select()
            ->from($this->_table, $this->getField())
            ->where($this->getPostField() . ' = ?', (int) $postId)
            ->where($this->getField() .'=?', (int) $id);

        return $adapter->fetchCol($select);
    }

    /**
     * Add rows to specific tables of relations (category, website, product, related posts)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @param array $addIds
     * @return $this
     */
    public function _addRelations(array $addIds)
    {
        $write = $this->_getWriteAdapter();
        $data = array();
        try {
            foreach ($addIds as $addId) {
                if (empty($addId)) {
                    continue;
                }
                $data[] = array(
                    $this->getPostField() => (int) $this->getPostId(),
                    $this->getField() => (int) $addId,
                );
            }

            if ($data) {
                $write->insertMultiple($this->_table, $data);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Remove rows from specific tables of relations (category, website, product, related posts)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @param array $removeIds
     * @return $this
     */
    public function _removeRelations(array $removeIds)
    {
        $write = $this->_getWriteAdapter();
        try {
            if (!empty($removeIds)) {
                $relationsToRemove = implode(',', $removeIds);
                $where = array(
                    $this->getPostField() . ' = ?' => (int) $this->getPostId(),
                    $this->getField() . ' IN (' . $relationsToRemove . ')'
                );

                $write->delete($this->_table, $where);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Add rows to specific tables of relations based on store id dependancy 
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $id
     * @param array $storeIds
     * @param array $addIds
     * @return $this
     */
    public function _addStoreDependableRelations(array $storeIds, array $addIds)
    {
        if (!count($addIds)) {
            return $this;
        }

        $write = $this->_getWriteAdapter();
        $data = array();

        try {
            foreach ($addIds as $addId) {
                if (empty($addId)) {
                    continue;
                }

                foreach ($storeIds as $storeId) {
                    $data[] = array(
                        $this->getPostField() => (int) $this->getPostId(),
                        $this->getField() => (int) $addId,
                        'store_id' => (int) $storeId
                    );
                }
            }

            if ($data) {
                $write->insertOnDuplicate($this->_table, $data);
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Remove rows from specific tables of relations based on the given store id
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $id
     * @param array storeIds
     * @param array $removeIds
     * @return $this
     */
    public function _removeStoreDependableRelations(array $storeIds, array $removeIds)
    {
        $write = $this->_getWriteAdapter();

        if (empty($removeIds)) {
            return $this;
        }

        try {
            $relationsToRemove = implode(',', $removeIds);
            $stores = implode(',', $storeIds);

            $condition = "( store_id IN (" . $stores . ") " .
                ' AND ' . $write->quoteInto($this->getPostField() . " = ?", $this->getPostId()) .
                ' AND ' . $this->getField() . ' IN (' . $relationsToRemove . ') )';

            $write->delete($this->_table, $condition);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

}
