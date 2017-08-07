<?php

/**
 * Post helper with specific methods for post.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Helper_Post extends Evozon_Blog_Helper_Data
{
    /**
     * Get the content to be displayed when listing the posts
     *
     * @param Evozon_Blog_Model_Post $post
     * @return string
     */
    public function getListingContent($post)
    {
        $content = '';
        $contentDisplay = Mage::getModel('evozon_blog/config')->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_DISPLAY_CONTENT);

        switch ($contentDisplay) {
            case Evozon_Blog_Model_Adminhtml_System_Config_Source_Post_Components::POST_COMPONENT_FULL_CONTENT:
                $content = $post->getPostContent();
                break;

            case Evozon_Blog_Model_Adminhtml_System_Config_Source_Post_Components::POST_COMPONENT_SHORT_CONTENT:
                $content = $post->generateShortContent(
                    (int) $this->getConfigModel()->getPostListingConfig(Evozon_Blog_Model_Config_Post::LISTING_TEASER_WORDS_COUNT)
                );
                break;
        }

        return $content;
    }

    /**
     * Create a restrictions rendering block
     *
     * @param Evozon_Blog_Model_Restriction_Rule_Interface_Rule $rule
     * @return mixed
     */
    public function getRestrictionRendererBlock($rule)
    {
        return Mage::app()
            ->getLayout()
            ->createBlock($rule->getRendererName())
            ->setModel($rule)
            ->setForm($rule->getForm());
    }

}
