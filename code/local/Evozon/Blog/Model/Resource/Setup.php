<?php

/**
 * Resource Setup
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Setup extends Mage_Eav_Model_Entity_Setup
{

    /**
     * Prepare blog posts attribute values to save
     *
     * @param array $attr
     * @return array
     */
    protected function _prepareValues($attr)
    {
        $data = parent::_prepareValues($attr);

        $data = array_merge($data, array(
            'frontend_input_renderer' => $this->_getValue($attr, 'input_renderer'),
            'is_global' => $this->_getValue(
                $attr, 'global', Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL
            ),
            'is_visible' => $this->_getValue($attr, 'visible', 1),
            'is_searchable' => $this->_getValue($attr, 'searchable', 0),
            'is_visible_on_front' => $this->_getValue($attr, 'visible_on_front', 0),
            'is_wysiwyg_enabled' => $this->_getValue($attr, 'wysiwyg_enabled', 0),
            'is_html_allowed_on_front' => $this->_getValue($attr, 'is_html_allowed_on_front', 0),
            'used_in_post_listing' => $this->_getValue($attr, 'used_in_post_listing', 0),
            'used_for_sort_by' => $this->_getValue($attr, 'used_for_sort_by', 0),
            'position' => $this->_getValue($attr, 'position', 0),
        ));
        return $data;
    }

    /**
     * Default entites and attributes
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'evozon_blog_post' => array(
                'entity_model' => 'evozon_blog/post',
                'attribute_model' => 'evozon_blog/resource_eav_attribute',
                'table' => 'evozon_blog/post',
                'additional_attribute_table' => 'evozon_blog/eav_attribute',
                'entity_attribute_collection' => 'evozon_blog/post_attribute_collection',
                'attributes' => array(
                    'title' => array(
                        'type' => 'varchar',
                        'label' => 'Title',
                        'input' => 'text',
                        'sort_order' => 1,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'searchable' => true,
                        'required' => true,
                        'visible' => true,
                        'used_in_post_listing' => 1,
                        'used_for_sort_by' => true,
                        'note' => Mage::helper('evozon_blog')->__("Enter post title, max. 255 characters long."),
                        'group' => 'Content',
                    ),
                    'post_content' => array(
                        'type' => 'text',
                        'label' => 'Post Content',
                        'input' => 'textarea',
                        'sort_order' => 2,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'searchable' => true,
                        'required' => true,
                        'visible' => true,
                        'visible_on_front' => true,
                        'wysiwyg_enabled' => true,
                        'is_html_allowed_on_front' => true,
                        'input_renderer' => 'evozon_blog/adminhtml_post_edit_form_renderer_wysiwyg',
                        'note' => Mage::helper('evozon_blog')->__("Main content. You can add rich content, images, product widgets and others."),
                        'group' => 'Content',
                    ),
                    'short_content' => array(
                        'type' => 'text',
                        'label' => 'Short Content',
                        'input' => 'textarea',
                        'sort_order' => 3,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'searchable' => true,
                        'required' => false,
                        'visible' => true,
                        'used_in_post_listing' => 1,
                        'is_html_allowed_on_front' => true,
                        'note' => Mage::helper('evozon_blog')->__("Teaser to be shown in listing. If you leave it empty, defined number of words will be taken from main content."),
                        'group' => 'Content',
                    ),
                    'url_key' => array(
                        'type' => 'varchar',
                        'label' => 'URL Key',
                        'input' => 'text',
                        'unique' => true,
                        'sort_order' => 4,
                        'required' => false,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'used_in_post_listing' => true,
                        'note' => Mage::helper('evozon_blog')->__("Custom url key. If you leave it empty, in case of it beeing needed for the post url, it will be auto-generated from Post title. Url-key must be unique overall, so if duplicate found will be appended with a number."),
                        'group' => 'Content',
                    ),
                    'url_structure' => array(
                        'type' => 'varchar',
                        'label' => 'Post URL Structure',
                        'input' => 'text',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible' => false,
                        'required' => false,
                        'visible_on_front' => false,
                        'default' => '',
                        'user_defined' => false,
                        'note' => Mage::helper('evozon_blog')->__("URL structure"),
                        'group' => 'General'
                    ),
                    'meta_title' => array(
                        'type' => 'varchar',
                        'label' => 'Meta Title',
                        'input' => 'text',
                        'required' => false,
                        'sort_order' => 1,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'required' => false,
                        'note' => Mage::helper('evozon_blog')->__("Blog post title which will fill the title tag in html head."),
                        'group' => 'Meta Information',
                    ),
                    'meta_keywords' => array(
                        'type' => 'text',
                        'label' => 'Meta Keywords',
                        'input' => 'textarea',
                        'required' => false,
                        'sort_order' => 2,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'note' => Mage::helper('evozon_blog')->__("Blog post keywords for for meta tag in header."),
                        'group' => 'Meta Information',
                    ),
                    'meta_description' => array(
                        'type' => 'varchar',
                        'label' => 'Meta Description',
                        'input' => 'textarea',
                        'required' => false,
                        'class' => 'validate-length maximum-length-255',
                        'sort_order' => 3,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'note' => Mage::helper('evozon_blog')->__("Post description that will appear as metatag in header. No more than 255 characters should be added."),
                        'group' => 'Meta Information',
                    ),
                    'status' => array(
                        'type' => 'int',
                        'label' => 'Status',
                        'input' => 'select',
                        'source' => 'evozon_blog/adminhtml_post_status',
                        'sort_order' => 7,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'required' => true,
                        'default' => Evozon_Blog_Model_Adminhtml_Post_Status::BLOG_POST_STATUS_DRAFT,
                        'searchable' => true,
                        'used_in_post_listing' => true,
                        'group' => 'Content',
                    ),
                    'comment_status' => array(
                        'type' => 'int',
                        'label' => 'Comment Status',
                        'input' => 'select',
                        'source' => 'eav/entity_attribute_source_boolean',
                        'sort_order' => 8,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'required' => true,
                        'default' => 1,
                        'note' => Mage::helper('evozon_blog')->__("Allow comments for this blog post."),
                        'group' => 'General',
                    ),
                    'admin_id' => array(
                        'type' => 'int',
                        'label' => 'Admin ID',
                        'input' => 'hidden',
                        'sort_order' => 9,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'required' => false,
                        'searchable' => true,
                        'used_in_post_listing' => true,
                        'visible' => true,
                        'group' => 'General'
                    ),
                    'category_ids' => array(
                        'type' => 'static',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                        'required' => false,
                        'sort_order' => 13,
                        'visible' => false,
                    ),
                    'created_at' => array(
                        'type' => 'static',
                        'label' => 'Created at',
                        'input' => 'label',
                        'backend' => 'evozon_blog/post_attribute_backend_createdAt',
                        'sort_order' => 19,
                        'visible' => true,
                        'required' => false,
                        'group' => 'General'
                    ),
                    'updated_at' => array(
                        'type' => 'static',
                        'label' => 'Updated at',
                        'input' => 'label',
                        'backend' => 'eav/entity_attribute_backend_time_updated',
                        'sort_order' => 20,
                        'visible' => true,
                        'required' => false,
                        'group' => 'General'
                    ),
                    'publish_date' => array(
                        'type' => 'datetime',
                        'label' => 'Published date',
                        'input' => 'date',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'backend' => 'eav/entity_attribute_backend_datetime',
                        'sort_order' => 21,
                        'visible' => true,
                        'required' => false,
                        'used_for_sort_by' => true,
                        'note' => Mage::helper('evozon_blog')->__("If you want to schedule the article to be visible in frontend later, set the date."),
                        'group' => 'Content',
                    ),
                    'restriction_id' => array(
                        'type' => 'int',
                        'label' => 'Restriction Rule ID',
                        'input' => 'text',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible' => false,
                        'required' => false,
                        'visible_on_front' => false,
                        'default' => null,
                        'user_defined' => true,
                        'note' => Mage::helper('evozon_blog')->__("Set the restriction rule ID on post"),
                        'group' => 'General'
                    ),
                    'store_visibility' => array(
                        'type' => 'int',
                        'label' => 'Store Visibility',
                        'input' => 'select',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'source' => 'catalog/product_status',
                        'visible' => true,
                        'required' => true,
                        'visible_on_front' => false,
                        'default' => 1,
                        'user_defined' => true,
                        'position' => 1,
                        'sort_order' => 1,
                        'note' => Mage::helper('evozon_blog')->__("If post`s visibility is 'Enabled', it will be displayed on the selected store view."),
                        'group' => 'General'
                    ),
                    'post_media' => array(
                        'type' => 'varchar',
                        'label' => 'Media',
                        'input' => 'gallery',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'backend' => 'evozon_blog/post_attribute_backend_media',
                        'visible' => true,
                        'required' => false,
                        'default' => null,
                        'user_defined' => false,
                        'group' => 'Media'
                    ),
                    'image' => array(
                        'type' => 'varchar',
                        'label' => 'Base image',
                        'input' => 'media_image',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'frontend' => 'evozon_blog/post_attribute_frontend_media',
                        'visible' => true,
                        'required' => false,
                        'default' => null,
                        'user_defined' => false,
                        'group' => 'Media'
                    ),
                    'small_image' => array(
                        'type' => 'varchar',
                        'label' => 'Small Image',
                        'input' => 'media_image',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'frontend' => 'evozon_blog/post_attribute_frontend_media',
                        'visible' => true,
                        'required' => false,
                        'default' => null,
                        'user_defined' => false,
                        'group' => 'Media'
                    ),
                    'thumbnail' => array(
                        'type' => 'varchar',
                        'label' => 'Thumbnail',
                        'input' => 'media_image',
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'frontend' => 'evozon_blog/post_attribute_frontend_media',
                        'visible' => true,
                        'required' => false,
                        'default' => null,
                        'user_defined' => false,
                        'group' => 'Media'
                    ),
                )
            ),
            'evozon_blog_tags' => array(
                'entity_model' => 'evozon_blog/tag',
                'attribute_model' => 'evozon_blog/resource_eav_attribute',
                'table' => 'evozon_blog/tag',
                'additional_attribute_table' => 'evozon_blog/eav_attribute',
                'entity_attribute_collection' => 'evozon_blog/tag_attribute_collection',
                'attributes' => array(
                    'name' => array(
                        'type' => 'varchar',
                        'label' => 'Name',
                        'input' => 'text',
                        'sort_order' => 1,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'searchable' => true,
                        'required' => true,
                        'visible' => true,
                        'used_in_post_listing' => 1,
                        'used_for_sort_by' => true,
                        'unique' => true,
                        'note' => Mage::helper('evozon_blog')->__("Enter tag name, max. 255 characters long.")
                    ),
                    'url_key' => array(
                        'type' => 'varchar',
                        'label' => 'URL Key',
                        'input' => 'hidden',
                        'unique' => true,
                        'sort_order' => 2,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'required' => false,
                        'used_in_post_listing' => true,
                        'note' => Mage::helper('evozon_blog')->__("Custom url key. It will be auto-generated from Tag name. Url-key must be unique overall.")
                    ),
                    'count' => array(
                        'type' => 'int',
                        'label' => 'Count',
                        'input' => 'text',
                        'sort_order' => 3,
                        'global' => Evozon_Blog_Model_Resource_Eav_Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'default' => 0,
                        'searchable' => true,
                        'used_in_post_listing' => true
                    ),
                    'created_at' => array(
                        'type' => 'static',
                        'label' => 'Created at',
                        'input' => 'date',
                        'backend' => 'evozon_blog/post_attribute_backend_createdAt',
                        'sort_order' => 4,
                        'visible' => false,
                    )
                )
            )
        );

        return $entities;
    }

}
