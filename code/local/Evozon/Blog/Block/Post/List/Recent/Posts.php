<?php

/**
 * Block that displays the recent posts widget or block on a blog-type category
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Post_List_Recent_Posts extends Evozon_Blog_Block_Post_Abstract
    implements Mage_Widget_Block_Interface
{

    /**
     * The function will give the proper layout, required by the admin in backend
     * If the widget is enabled, we are displaying it
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function __construct()
    {
        parent::__construct();

        if ($this->getIsEnabled()) {
            $this->setTemplate($this->getDisplay());
        }
    }

    /**
     * Getting the template according to the type of page we are on
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    protected function getDisplay()
    {
        $template = $this->getTemplate();
        if ($this->_isBlog) {
            $template = $this->_getConfigData(Evozon_Blog_Model_Config_Post::RECENT_POSTS_TEMPLATE);
        }

        return $template;
    }

    /**
     * Accessing the system configurations/widget configurations and retrieving the needed params
     * Before retrieving the values, we have to check if our Recent Posts instance is displayed in a category that acts like a Blog
     * If we have custom params for our widget instance, we will be using those.
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $param
     * @return int/string $value
     */
    public function getConfigData($param)
    {
        if ($this->_isBlog) {
            return $this->_getConfigData($param);
        }
        
        $value = $this->getData($param);
        if (!isset($value)) {
            $value = $this->_getConfigData($param);
        }

        return $value;
    }

    /**
     * The recent *published* blog posts will have to be filtered by the time they were posted
     * Depending on the store view
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Evozon_Blog_Model_Post
     */
    protected function getRecentPosts()
    {
        $blogCollection = $this->_getPostCollection();
        $blogCollection->setOrder('publish_date', 'desc');

        $blogCollection->setPageSize(
            (int) $this->getConfigData(Evozon_Blog_Model_Config_Post::RECENT_POSTS_NUMBER)
        );

        $blogCollection->setProperDateFormat();
        return $blogCollection;
    }

    /**
     * Accessing config model and retrieving data on it
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $key
     * @return int | string
     */
    protected function _getConfigData($key)
    {
        return $this->getConfigModel()->getPostRecentConfig($key);
    }

    /**
     * Getting is enabled value
     * 
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->getDataSetDefault('is_enabled', $this->_getConfigData(Evozon_Blog_Model_Config_Post::RECENT_POSTS_ENABLED));
    }

    /**
     * Return the url to post comments
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Post $post
     * @return string
     */
    public function getPostCommentsUrl($post)
    {
        return $post->getPostUrl() . '#post_comments';
    }

    /**
     * Creating the tags list for each post
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Post $post
     * @return string
     */
    public function getTagsListHtml($post)
    {
        $content = Mage::getModel('core/layout')
            ->createBlock('evozon_blog/post_list_tags')
            ->setPost($post);

        return $content->toHtml();
    }

}
