<?php

/**
 * Resource Model for Comment Model
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Comment extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * Object needed to be processed
     * @var Mage_Core_Model_Abstract
     */
    protected $_comment;

    /**
     * initialize resource: set main table and identifier
     *
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/comment', 'id');
    }

    /**
     * After delete actions, for example:
     * Recursively call delete on comment children
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Comment
     */
    protected function _afterDelete(Mage_Core_Model_Abstract $comment)
    {
        parent::_afterDelete($comment);
        $this->_deleteChildrenComments($comment->getId());
    }

    /**
     * Deleting children comments from the table after the comment has been deleted
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $id
     * @return \Evozon_Blog_Model_Resource_Comment
     */
    protected function _deleteChildrenComments($id)
    {
        $adapter = $this->_getWriteAdapter();

        try {
            $adapter->delete($this->getMainTable(), $adapter->quoteInto('parent_id = ?', (int)$id));
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Save data after comment save
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Mage_Core_Model_Abstract $comment
     * @return Evozon_Blog_Model_Resource_Post
     */
    protected function _afterSave(Mage_Core_Model_Abstract $comment)
    {
        parent::_afterSave($comment);

        $this->setComment($comment);
        $this->_saveContextData();
        $this->_changeChildrenAvailability();
    }

    /**
     * According to model logic, context data is made of:
     * - parent_id (if the comment has been a reply to some other comment)
     * - path & level (that have to be set accordingly)
     *
     * This action is called only on object creation
     * If the object has the Path set, there is nothing to save
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Comment
     */
    protected function _saveContextData()
    {
        $comment = $this->getComment();

        if ($comment->getPath()) {
            return $this;
        }

        if (!$comment->getParentId()) {
            $this->_savePath();
            return $this;
        }

        $this->_savePathAndLevel();
    }

    /**
     * Update path field with comment id
     * (used only when the added comment has no parent id, which makes it of level 0)
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Resource_Comment
     */
    protected function _savePath()
    {
        $id = $this->getComment()->getId();
        $this->_getWriteAdapter()->update(
            $this->getTable('evozon_blog/comment'),
            array('path' => $id),
            array('id = ?' => $id)
        );

        return $this;
    }

    /**
     * Update path and level with data from parent
     * This will be an update query with a select query to fetch data from the same table
     * The path will consist of parent path concatenated with comment id
     * The level will consist of counting the number of elements in parent comment`s path
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Evozon_Blog_Model_Resource_Comment
     */
    protected function _savePathAndLevel()
    {
        $adapter = $this->_getReadAdapter();
        $parentPath = $this->_getParentPath($this->getComment()->getParentId());

        $path = $adapter
            ->select()
            ->from(
                array('p' => $parentPath),
                array(new Zend_Db_Expr("CONCAT(p.path,','," . $this->getComment()->getId() . ") as path"))
            );

        $level = $adapter
            ->select()
            ->from(
                array('l' => $parentPath),
                array(new Zend_Db_Expr("(LENGTH(l.path) - LENGTH(REPLACE(l.path,',','')) +1) as level"))
            );

        $this->_getWriteAdapter()
            ->update(
                $this->getMainTable(),
                array(
                    'path' => new Zend_Db_Expr("(" . $path . ")"),
                    'level' => new Zend_Db_Expr("(" . $level . ")")
                ), array('id = ?' => $this->getComment()->getId())
            );

        return $this;
    }

    /**
     * SQL to select parent comment`s path
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $parentId
     * @return Zend_Db_Select
     */
    protected function _getParentPath($parentId)
    {
        return $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('path'))
            ->where('id = ?', (int)$parentId);
    }

    /**
     * Setting working object
     *
     * @param Mage_Core_Model_Abstract $comment
     * @return \Evozon_Blog_Model_Resource_Comment
     */
    protected function setComment($comment)
    {
        $this->_comment = $comment;
        return $this;
    }

    /**
     * Getting object
     *
     * @return Mage_Core_Model_Abstract (preferably of type Evozon_Blog_Model_Comment)
     */
    protected function getComment()
    {
        return $this->_comment;
    }

    /**
     * Returns comments` count for specific post
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return array
     */
    protected function _getCountSelectByPost($postId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(new Zend_Db_Expr('count(*) AS comment_count')))
            ->where('post_id = ?', $postId);

        return $select;
    }

    /**
     * Gets all comments` count for given post
     * It is used in BE in post edit to get comments number
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return array
     */
    public function getCountByPost($postId)
    {
        return $this->_getReadAdapter()->fetchRow($this->_getCountSelectByPost($postId));
    }

    /**
     * Gets filtered comments count for required post
     * It is used in post view to get comments number
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return array
     */
    public function getFilteredCountByPost($postId)
    {
        $select = $this->_addVisibilityFilters($this->_getCountSelectByPost($postId));
        return $this->_getReadAdapter()->fetchRow($select);
    }

    /**
     * Adding comment visibility filters for frontend
     * It is used to filter posts
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Zend_Db_Select $select
     * @return Zend_Db_Select
     */
    protected function _addVisibilityFilters(Zend_Db_Select $select)
    {
        $select
            ->where('enabled = ?', true)
            ->where(
                'status = ?', Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED
            );

        return $select;
    }

    /**
     * Creates a default select on the comments table
     * Adds comments visibility
     * It is used in a join with Evozon_Blog_Model_Resource_Post_Collection
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Model_Resource_Post_Collection
     * @param array $postIds
     * @return \Zend_Db_Select
     */
    public function getSelectQueryForPostsCollection(array $postIds)
    {
        $adapter = $this->_getReadAdapter();

        $select = $adapter
            ->select()
            ->from($this->getMainTable(), array('post_id', new Zend_Db_Expr('count(*) AS comment_count')))
            ->where('post_id IN (?)', $postIds)
            ->group('post_id');

        $this->_addVisibilityFilters($select);

        return $select;
    }

    /**
     * Retrieves the nr of first level subcomments to specific comment
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $commentId
     * @return int
     */
    public function getFirstLevelSubcommentsCountForComment($commentId)
    {
        $select = $this->firstLevelSubcommentsCountSql();
        $select->where('PARENTS.id = ?', $commentId);

        return $this->_getReadAdapter()->query($select)->fetchColumn(1);
    }

    /**
     * Creates a sql used in comments collection in order to join each comment to the number of direct replies
     * connected to it (also knows as first level subcomments)
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Zend_Db_Select
     */
    public function firstLevelSubcommentsCountSql()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('PARENTS' => $this->getMainTable()),
                array(
                    'comment_id' => 'PARENTS.id',
                    new Zend_Db_Expr('count(*) AS first_level_count')
                )
            )
            ->joinInner(
                array('CHILDREN' => $this->getMainTable()),
                'PARENTS.id = CHILDREN.parent_id',
                array()
            )
            ->group('comment_id');

        return $select;
    }

    /**
     * Creates a sql used in comments collection in order to join each comment to the number of total subcoments
     * (also known as all_levels_subcomments)
     * count(*)-1 was made in order to remove itself from the count
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Zend_Db_Select
     */
    public function _allLevelSubcommentsCountSql()
    {
        $select = $this->_getReadAdapter()->select()
            ->from(
                array('PARENTS' => $this->getMainTable()),
                array(
                    'comment_id' => 'PARENTS.id',
                    new Zend_Db_Expr('count(*)-1 AS all_levels_count')
                )
            )
            ->joinInner(
                array('CHILDREN' => $this->getMainTable()),
                new Zend_Db_Expr('FIND_IN_SET(PARENTS.id, CHILDREN.path)'),
                array()
            )
            ->group('comment_id');

        return $select;
    }

    /**
     * Change children enabled property to pending
     * If the parent comment after saving has a different status
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _changeChildrenAvailability()
    {
        if (!$this->getComment()->hasChangeStatusForChildren()) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $where = array();

        // conditions: level > 0 and $parentId is in path
        $where[] = $adapter->quoteInto('level > ?', '0');
        $where[] = $adapter->quoteInto('FIND_IN_SET(?, path)', $this->getComment()->getId());

        // update the entries
        $adapter->update(
            $this->getMainTable(), array(
            'enabled' => $this->getComment()->getEnabled()
        ), $where
        );

        return $this;
    }

    /**
     * Delete comments by status
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array|string $status
     */
    public function deleteByStatus($status)
    {
        if (!is_array($status)) {
            $status = array($status);
        }

        $adapter = $this->_getWriteAdapter();
        $where = $adapter->quoteInto('status IN(?)', $status);
        $adapter->delete($this->getMainTable(), $where);
    }

    /**
     * Delete comments by spam status
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $postsId
     */
    public function deleteBySpamStatus()
    {
        $this->deleteByStatus(Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_SPAM);
    }

    /**
     * If a comment has been marked as spam by the checker,
     * the status needs to be updated
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Model_Service_Spam_Checker
     * @param int $id
     */
    public function changeStatusToSpamById($id)
    {
        $adapter = $this->_getWriteAdapter();
        $where = $adapter->quoteInto('id = ?', $id);

        $adapter->update(
            $this->getMainTable(), array(
            'status' => Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_SPAM
            ), $where
        );
    }

    /**
     * Retrieve select object for load object data
     * Joins Customer and Admin tables
     * Creates a comment count field to store first level counts
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_Core_Model_Abstract $object
     * @return Zend_Db_Select
     * @TODO Move to Adapter
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        //get parent loadSelect
        $select = parent::_getLoadSelect($field, $value, $object);

        // make left join with admin and customer tables
        $select
            // left join with admin_user table to get admin's data
            ->joinLeft(
                array('admin' => 'admin_user'),
                $this->getMainTable() . '.admin_id = admin.user_id',
                array(
                    'admin_user_id' => 'admin.user_id',
                    'admin_firstname' => 'admin.firstname',
                    'admin_lastname' => 'admin.lastname',
                    'admin_email' => 'admin.email'
                )
            )
            // left join with customer_entity table to get customer's email
            ->joinLeft(
                array('customer' => 'customer_entity'),
                $this->getMainTable() . '.user_id = customer.entity_id',
                array('customer_email' => 'customer.email')
            )
            // left join with customer_entity_varchar to get the customer's firstname
            ->joinLeft(
                array('firstname' => 'customer_entity_varchar'),
                'firstname.entity_id = customer.entity_id AND firstname.attribute_id = 5',
                array('customer_firstname' => 'firstname.value')
            )
            // left join with customer_entity_varchar to get the customer's lastname
            ->joinLeft(
                array('lastname' => 'customer_entity_varchar'),
                'lastname.entity_id = customer.entity_id AND lastname.attribute_id = 7',
                array('customer_lastname' => 'lastname.value')
            );

        //checking (for BE) if there are any comments for the 1st level count
        $select->joinLeft(
            array('c' => new Zend_Db_Expr('( ' . $this->firstLevelSubcommentsCountSql() . ' )')),
            'id = c.comment_id',
            array('first_level_count' => 'c.first_level_count')
        );


        return $select;
    }

}
