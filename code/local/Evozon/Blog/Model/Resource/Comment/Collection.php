<?php

/**
 * Comment Collection
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Comment_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    /**
     * Comment entity collection
     * 
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _construct()
    {
        $this->_init('evozon_blog/comment');
    }

    /**
     * Mapping for fields
     * 
     * @var array
     */
    protected $_postAttributesMap = array('title', 'publish_date', 'status', 'comment_status');

    /**
     * Inner join with post table
     * to get relevant information about the post
     * that are grouped in the protected variable $_postAttributesMap
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function addPostDetails()
    {
        $select = $this->getSelect()->group('main_table.id');
        $select->joinInner(
            array('post' => $this->getTable('evozon_blog/post')),
            'post.entity_id = main_table.post_id', array()
        );

        $post = Mage::getResourceSingleton('evozon_blog/post');
        $adapter = $this->getConnection();

        foreach ($this->_postAttributesMap as $field) {
            $attr = $post->getAttribute($field);
            $joinExpr = $field . '.entity_id = main_table.post_id AND '
                . $adapter->quoteInto($field . '.entity_type_id = ?', $post->getTypeId()) . ' AND '
                . $adapter->quoteInto($field . '.attribute_id = ?', $attr->getAttributeId());

            $select->join(
                array($field => $attr->getBackend()->getTable()), $joinExpr, array('post_' . $field => 'value')
            );
        }

        return $this;
    }

    /**
     * Get the children of a given parent comments array
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $parentIds
     * @return \Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function getChildren(array $parentIds)
    {
        // get the comments collection filter by the status, the level and get info about the author
        $this
            ->addFieldToSelect(
                array(
                    'id', 'subject', 'content', 'store_id', 'created_at',
                    'user_id', 'admin_id', 'author', 'level', 'parent_id', 'path'
                )
            )
            ->addCustomerAndAdminJoin()
            ->addFieldToFilter('enabled', array('eq' => true))
            ->addFieldToFilter('level', array('gt' => '0'))
            ->addFieldToFilter('status', Evozon_Blog_Model_Adminhtml_Comment_Status::BLOG_COMMENT_STATUS_APPROVED);

        // filter the collection by the parent ids (parent id is in the path)
        $this->addFieldToFilter('path', $this->getParentConditions($parentIds));

        return $this;
    }

    /**
     * Return an array with the finset condition for each parent
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param array $parentIds
     * @return array
     */
    protected function getParentConditions($parentIds)
    {
        // get collection select
        $conditions = array();

        foreach ($parentIds as $parentId) {
            // add the finset condition
            $conditions[] = array('finset' => $parentId);
        }

        return $conditions;
    }

    /**
     * Get the data of the author from the admin and customer tables
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Resource_Post_Collection
     */
    public function addCustomerAndAdminJoin()
    {
        $this->getSelect()
            // left join with admin_user to get all the admin's data
            ->joinLeft(
                array('admin' => $this->getTable('admin/user')),
                'main_table.admin_id = admin.user_id',
                array(
                'admin_user_id' => 'admin.user_id',
                'admin_firstname' => 'admin.firstname',
                'admin_lastname' => 'admin.lastname',
                'admin_email' => 'admin.email'
                )
            )
            // left join with customer to get the customer's email
            ->joinLeft(
                array('customer' => $this->getTable('customer/entity')),
                'main_table.user_id = customer.entity_id',
                array('customer_email' => 'customer.email')
            )
            // left join with customer_entity_varchar to get the customer's firstname
            ->joinLeft(
                array('firstname' => $this->getConnection()->getTableName('customer_entity_varchar')),
                'firstname.entity_id = customer.entity_id AND firstname.attribute_id = 5',
                array('customer_firstname' => 'firstname.value')
            )
            // left join with customer_entity_varchar to get the customer's lastname
            ->joinLeft(
                array('lastname' => $this->getConnection()->getTableName('customer_entity_varchar')),
                'lastname.entity_id = customer.entity_id AND lastname.attribute_id = 7',
                array('customer_lastname' => 'lastname.value')
        );

        return $this;
    }

    /**
     * Get SQL for get record count
     * Override parent method to verify the case when exists group by in select
     * because the count return a wrong number
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);

        // verify if exists group by in select
        if (count($this->getSelect()->getPart(Zend_Db_Select::GROUP)) > 0) {
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->distinct(true);
            $group = $this->getSelect()->getPart(Zend_Db_Select::GROUP);
            $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
        } else {
            $countSelect->columns('COUNT(*)');
        }

        return $countSelect;
    }

    /**
     * Used in BE to get all subcomments for a comment when editing/view-ing it
     * Filters the existing collection by parent_id and orders them by most recent
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Block_Adminhtml_Comment_List function getComments()
     * @param int $parentId
     * @return \Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function getChildrenCommentsByParentId($parentId)
    {
        $this->addFieldToFilter('parent_id', array('eq' => (int) $parentId))
            ->setOrder('created_at', 'DESC');

        return $this;
    }

    /**
     * Returns first level comments depending on the post id
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Block_Adminhtml_Comment_List function getComments()
     * @param int $postId
     * @return \Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function getChildrenCommentsByPostId($postId)
    {
        $this->addFieldToFilter('parent_id', array('eq' => 0))
            ->addFieldToFilter('post_id', array('eq' => (int) $postId))
            ->setOrder('created_at', 'DESC');

        return $this;
    }
    
    /**
     * To the comments table will be added 2 more columns:
     * - first_level_count (count of the first level comments - that have current comment as parent_id)
     * - all_subcomments_count (count of all the subcomments - that have current comment in path and the level greater than the comment itself)
     * This way there will be avoided 2 more db calls (per comment) in order to retrieve needed information
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @see Evozon_Blog_Block_Adminhtml_Comment_List, Evozon_Blog_Block_Customer_Account_Comment_List
     * @return \Evozon_Blog_Model_Resource_Comment_Collection
     */
    public function addSubcommentsCountData()
    {
        $firstLevelCountSelect = Mage::getResourceModel('evozon_blog/comment')
            ->firstLevelSubcommentsCountSql();

        $allLevelSubcommentsCountSelect = Mage::getResourceModel('evozon_blog/comment')
            ->_allLevelSubcommentsCountSql();
        
        $this->getSelect()
            ->joinLeft(
                array('c'=> new Zend_Db_Expr('( '.$firstLevelCountSelect.' )')),
                'main_table.id = c.comment_id', array('first_level_count')
            )
            ->joinLeft(
                array('d'=> new Zend_Db_Expr('( '.$allLevelSubcommentsCountSelect.' )')),
                'main_table.id = d.comment_id', array('all_levels_count')
            );
        
        return $this;
    }

    /**
     * Set the locale dates for comments collection
     * If the function is used from back end the created_at and publish_date will be set,
     * else only the publish_date with the format from system config
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function setProperDateFormat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            $this->setProperDateFormatForAdmin();

            return $this;
        }
        
        $this->setProperDateFormatForCustomer();

        return $this;
    }
    
    /**
     * Set the proper created at date for the back end
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForAdmin()
    {
        foreach ($this->getItems() as $comment) {
            $comment->setCreatedAt($this->getLocaleDate($comment->getCreatedAt()));
        }
    }
    
    /**
     * Set the proper created at date for the front end
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function setProperDateFormatForCustomer()
    {
        foreach ($this->getItems() as $comment) {
            $comment->setCreatedAt($this->getTimeByFormatAndLocale($comment->getCreatedAt()));
        }
    }
    
    /**
     * Return the date converted in the locale date
     * 
     * @TODO   Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getLocaleDate($date) {
        return Mage::helper('evozon_blog')->getLocaleDate($date);
    }
    
    /**
     * Return the date converted in the locale date and in the default locale from system config
     * 
     * @TODO   Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getTimeByFormatAndLocale($date) {
        return Mage::helper('evozon_blog')->getTimeByFormatAndLocale($date);
    }
}
