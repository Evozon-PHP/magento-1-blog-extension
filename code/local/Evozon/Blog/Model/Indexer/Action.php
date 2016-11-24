<?php
/**
 * Evozon Blog Indexer Action class
 * decides what kind of indexing is required:
 * -full (when all the post url need to be reindexed)
 * -row  (when only a specific post for a specific store is reindexed)
 * -list (when a post (or more) for all existing stores will be reindexed)
 *
 * The url reindexing model sets the event object
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Indexer_Action
{
    /**
     * action model to call reindexing on a row
     */
    const EVOZON_BLOG_FACTORY_ACTION_ROW_MODEL = 'evozon_blog/indexer_action_row';

    /**
     * action model used to reindex a list
     */
    const EVOZON_BLOG_FACTORY_ACTION_LIST_MODEL = 'evozon_blog/indexer_action_list';

    /**
     * action model used to reindex all the post urls
     */
    const EVOZON_BLOG_FACTORY_ACTION_FULL_MODEL = 'evozon_blog/indexer_action_full';

    /**
     * @var null | Varien_Object
     */
    protected $_post = null;

    /**
     * Applying reindexing on the specific scenario
     * If there is no post set, then a full reindexing has been called
     * If a store id is present, a row reindexing is required
     * In all the rest of the scenarios (mass actions, etc) - a list reindex will do
     * 
     * @return mixed
     * @throws Exception
     */
    public function reindex()
    {
        $post = $this->getPost();
        if (is_null($post))
        {
            return $this->getFullActionModel()->reindex();
        }

        if ($post->getStoreId())
        {
            return $this->getRowActionModel()->reindex();
        }

        return $this->getListActionModel()->reindex();
    }

    /**
     * Removing indexing data for selected post ids
     * (ex: on mass delete action)
     */
    public function clearUrlRewrites()
    {
        try {
            $this->getListActionModel()->deleteRewrites();
        } catch (Evozon_Blog_Model_Exception_IndexFactory $exc)
        {
            Mage::logException($exc);
        } catch (Exception $exc)
        {
            Mage::logException($exc);
        }

        return $this;
    }

    /**
     * Full action model to reindex data
     * @return Mage_Core_Model_Abstract
     */
    public function getFullActionModel()
    {
        return Mage::getModel(self::EVOZON_BLOG_FACTORY_ACTION_FULL_MODEL);
    }

    /**
     * Row action model to reindex data
     * @return Mage_Core_Model_Abstract
     */
    public function getRowActionModel()
    {
        $post = $this->getPost();
        return Mage::getModel(self::EVOZON_BLOG_FACTORY_ACTION_ROW_MODEL)
            ->setPostIds(array($post->getId()))
            ->setStoreId($post->getStoreId());
    }

    /**
     * List action model to reindex data
     * On a single post or on multiple ones
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getListActionModel()
    {
        return Mage::getModel(self::EVOZON_BLOG_FACTORY_ACTION_LIST_MODEL)
            ->setPostIds($this->getIds());
    }

    /**
     * Setting the post object
     * It is required for the row or listing reindexing
     *
     * @param Varien_Object $post
     */
    public function setPost(Varien_Object $post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * Getting post object
     * @return mixed
     */
    public function getPost()
    {
        return $this->_post;
    }

    /**
     * Setting post ids used in list action in order
     * It can be an array of ids (ex: a delete event triggered from mass action)
     * or a single id (for a single post)
     *
     * @return array
     */
    public function getIds()
    {
        $ids = $this->getPost()->getId();
        if (!is_array($ids))
        {
            return array($ids);
        }

        return $ids;
    }

}