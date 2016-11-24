<?php

/**
 * Resource Model for Post Model
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post extends Mage_Catalog_Model_Resource_Abstract
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_post';

    /**
     * @var array
     */
    protected static $_defaultAttributes = array(
        'entity_id',
        'entity_type_id',
        'attribute_set_id',
        'created_at',
        'updated_at',
    );

    /**
     * Initialize resource: set main table and identifier
     */
    public function __construct()
    {
        parent::__construct();

        $this->setType(Evozon_Blog_Model_Post::ENTITY);
        $this->setConnection('evozon_blog_read', 'evozon_blog_write');
    }

    /**
     * Default post attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return self::$_defaultAttributes;
    }

    /**
     * Wrapper for main table getter
     *
     * @access public
     * @return string
     */
    public function getMainTable()
    {
        return $this->getEntityTable();
    }

    /**
     * Save data related with post
     *
     * @param Varien_Object $post
     * @return Evozon_Blog_Model_Resource_Post
     */
    protected function _afterSave(Varien_Object $post)
    {
        parent::_afterSave($post);
        
        $this->_saveRestrictions($post);
    }

    /**
     * Access admin table and retrieve owner details based on the attribute`s value for the model
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getOwner(Evozon_Blog_Model_Post $post)
    {
        $adapter = $this->_getReadAdapter();

        $adminId = $this->getAttributeRawValue($post->getId(), 'admin_id', $post->getStoreId());
        $select = $adapter->select()
            ->from(array('admin' => $this->getTable('admin/user')), array(
                'admin_id' => 'admin.user_id',
                'admin_user_id' => 'admin.user_id',
                'author_firstname' => 'admin.firstname',
                'author_lastname' => 'admin.lastname',
                'author_email' => 'admin.email'
                )
            )
            ->where('user_id = ?', (int) $adminId);

        return $adapter->fetchRow($select);
    }

    /**
     * Return assigned images for specific stores
     *
     * @param Evozon_Blog_Model_Post $post
     * @param int|array $storeIds
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getAssignedImages(Evozon_Blog_Model_Post $post, $storeIds)
    {
        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $mainTable = $post->getResource()->getAttribute('image')
            ->getBackend()
            ->getTable();

        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(
                array('images' => $mainTable), array('value as filepath', 'store_id')
            )
            ->joinLeft(
                array('attr' => $this->getTable('eav/attribute')), 'images.attribute_id = attr.attribute_id', array(
                'attribute_code')
            )
            ->where('entity_id = ?', $post->getId())
            ->where('store_id IN (?)', $storeIds)
            ->where('attribute_code IN (?)', array('small_image', 'thumbnail', 'image'));

        $images = $read->fetchAll($select);

        return $images;
    }

    /**
     * The data about images is stored in the "evozon_blog_post_image" and "evozon_blog_post_image_value" tables
     * Depending on the store view, different values for label, href, position and disabled properties can be set
     * It is required two left joins within the "evozon_blog_post_image_value" table in order to set values for the specific store or leave the default ones
     * This is the basic select & join that will be used for further filtering for backend and frontend display
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $storeId
     * @return Varien_Db_Select
     */
    protected function getImagesSelect($storeId)
    {
        $adapter = $this->_getReadAdapter();

        $positionCheckSql = $adapter->getCheckSql('image_value.position IS NULL', 'default_value.position', 'image_value.position');

        // Select gallery images for post
        $select = $adapter->select()
            ->from(
                array('main' => $this->getTable('evozon_blog/post_image')), array(
                'value_id', 'value', 'post_id' => 'entity_id')
            )
            ->joinLeft(
                array('image_value' => $this->getTable('evozon_blog/post_image_value')), $adapter->quoteInto('main.value_id = image_value.value_id AND image_value.store_id = ?', (int) $storeId), array(
                'label', 'position', 'disabled', 'href', 'store_id')
            )
            ->joinLeft(
                array('default_value' => $this->getTable('evozon_blog/post_image_value')), 'main.value_id = default_value.value_id AND default_value.store_id = 0', array(
                'label_default' => 'label',
                'position_default' => 'position',
                'disabled_default' => 'disabled',
                'href_default' => 'href',
                'store_default' => 'store_id'
                )
            )
            ->order($positionCheckSql . ' ' . Varien_Db_Select::SQL_ASC);

        return $select;
    }

    /**
     * Return the images for post
     * Only the images that are not disabled will be shown
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $postId
     * @return array
     */
    public function getImages($postId, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->from($this->getImagesSelect($storeId))
            ->where('post_id = ?', $postId)
            ->where('disabled = ?', 0);

        return $adapter->fetchAll($select);
    }

    /**
     * Return the post images for frontend gallery
     * 
     * @param type $imageIds
     * @return array
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function getGalleryImages($imageIds, $storeId)
    {
        // init read adapter
        $adapter = $this->_getReadAdapter();

        // select the images only from the current gallery
        $select = $adapter->select()
            ->from($this->getImagesSelect($storeId))
            ->where('FIND_IN_SET(value_id, ?)', $imageIds)
            ->where('disabled = ?', 0);

        return $adapter->fetchAll($select);
    }

    /**
     * Retrieve post categories
     * @TODO Implement correct caching solution for posts
     *
     * @param Mage_Catalog_Model_Product $object
     * @return array
     */
    public function getCategoryIdsWithAnchors($object)
    {
        $selectRootCategories = $this->_getReadAdapter()->select()
            ->from(
                array($this->getTable('catalog/category')), array('entity_id')
            )
            ->where('level <= 1');
        $rootIds = $this->_getReadAdapter()->fetchCol($selectRootCategories);
        $select = $this->_getReadAdapter()->select()
            ->from(
                array($this->getTable('catalog/category_product_index')), array(
                'category_id')
            )
            ->where('product_id = ?', (int) $object->getEntityId())
            ->where('category_id NOT IN(?)', $rootIds);

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Save the post restrictions object tree
     *
     * @param Evozon_Blog_Model_Post $object
     * @return $this
     */
    protected function _saveRestrictions(Evozon_Blog_Model_Post $object)
    {
        $restrictions = $object->getRestrictions();

        try {
            $restrictions->loadPostData($object);
            $restrictions->setPostId($object->getId());

            $restrictions->save();
        } catch (Exception $exc) {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|Mage_Core_Model_Store $storeId
     * @return Evozon_Blog_Model_Resource_Post
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof Mage_Core_Model_Store) {
            $storeId = $storeId->getId();
        }

        $this->_storeId = (int) $storeId;
        return $this;
    }    
    
    /**
     * Used by post_tag model in order to get access to which stores are using the default values as well
     * to be able to modify the count properly while editing the post on default store
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $id
     * @return array
     */
    public function getStoresStatusOnUsingDefaultValuesByPostId($id)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter
            ->select()
            ->from(
                array('e' => 'evozon_blog_post_entity'), array('entity_id' => 'entity_id')
            )
            ->where('e.entity_id=?', (int) $id);

        $attributes = array('status','store_visibility');
        foreach ($attributes as $attrCode) {
            $attr = Mage::getModel('eav/config')->getAttribute(self::ENTITY, $attrCode);
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackendTable();

            $alias = self::ENTITY . '_' . $attrCode;

            $innerCondition = array(
                $adapter->quoteInto("{$attrCode}_default.entity_id = e.entity_id", ''),
                $adapter->quoteInto("{$attrCode}_default.attribute_id = ?", $attrId),
                $adapter->quoteInto("{$attrCode}_default.store_id = ?", 0)
            );
            $joinLeftConditions = array(
                $adapter->quoteInto("{$alias}.entity_id = e.entity_id", ''),
                $adapter->quoteInto("{$alias}.attribute_id = ?", $attrId)
            );

            $select
                ->joinInner(
                    array($attrCode . '_default' => $attrTable), implode(' AND ', $innerCondition),
                    array($attrCode . '_default' => 'value')
                )
                ->joinLeft(
                    array($alias => $attrTable), implode(' AND ', $joinLeftConditions),
                    array($attrCode => 'value', $attrCode.'_store'=>'store_id')
            );
        }
        
        $storeValidation = $adapter
            ->select()
            ->distinct()
            ->from(
                array('joins' => $select),
                array(
                    new Zend_Db_Expr("IF (joins.store_visibility_store > 0, joins.store_visibility_store, joins.status_store) AS 'store'"),
                    new Zend_Db_Expr("IF (joins.store_visibility = joins.store_visibility_default AND joins.status = joins.status_default, 1, 0) AS 'is_valid'")
                )
            );
        
        return $adapter->fetchPairs($storeValidation);
    }
    
    
    /**
     * When deleting a post, the tags attached to it must be decremented
     * The stores on which the decrement will happen are the ones where the post is visible and enabled
     * (and also where the post is selected to be displayed on the website)
     * 1. store_visibility (enabled)
     * 2. status (published)
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getStoresWhereEntityIsEnabledAndVisibleByPostId($postId)
    {
        $adapter = $this->_getReadAdapter();
        $enabled = Evozon_Blog_Model_Post::EVOZON_BLOG_POST_VISIBILITY_ENABLED;
        $published = Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_PUBLISHED;

        $attrTable = $this->getAttribute('status')->getBackend()->getTable();
        $select = $adapter
            ->select()
            ->from(
                array(
                    'joins' => $adapter->select()
                    ->from(array('a' => $attrTable), array('store_id','status'=>'value'))
                    ->where('a.attribute_id = ?', $this->getAttribute('store_visibility')->getAttributeId())
                    ->join(
                        array('j'=>$attrTable), 
                        $adapter->quoteInto("(j.attribute_id = {$this->getAttribute('status')->getAttributeId()} "
                        . "AND j.entity_id={$postId} AND j.store_id = a.store_id)",''),               
                        array('store_visibility'=>'value')
                    )
                    ->where('a.entity_id = ?', $postId)
                ),
                array(
                    'store' => 'joins.store_id',
                    new Zend_Db_Expr("IF (joins.store_visibility = {$enabled} AND joins.status = {$published}, 1, 0) AS 'is_valid'")
                )
            );
        
        return $adapter->fetchPairs($select);
    }
}
