<?php

/**
 * Related posts list, will display related posts to current post
 *
 * @package     Evozon_Blog
 * @author      Calin Florea <calin.florea@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_View_Posts extends Evozon_Blog_Block_Post_Abstract
{

    /**
     * The block will be rendered only if the configurations allow us to
     */
    protected function _construct()
    {
        if ($this->getIsEnabled()) {
            parent::_construct();
        }
    }

    /**
     * Set the template file
     *
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/view/posts.phtml';
    }

    /**
     * returns related post collection filtered and sorted
     *
     * @author  Calin Florea <calin.florea@evozon.com>
     * @return  null|Evozon_Blog_Model_Resource_Post_Collection
     */
    public function getRelatedPostsCollection()
    {
        $post = $this->getPost();

        $postCollection = parent::_getPostCollection();
        $postCollection->addAttributeToFilter('entity_id', array('in' => Mage::getResourceModel('evozon_blog/post_relations_related')->getIdsByPostId($post->getId())));

        $limit = (int) $this->getConfigModel()->getPostRelatedConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_NUMBER);
        if ($limit) {
            $postCollection->setPageSize($limit);
        }

        return $postCollection;
    }

    /**
     * Getting is enabled status from config
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getDataSetDefault(
            'is_enabled',
            $this->getConfigModel()->getPostRelatedConfig(Evozon_Blog_Model_Config_Post::RELATED_POSTS_ENABLED)
        );
    }

}
