<?php

/**
 * Blog Configuration model. Get all configuration available through this model.
 *
 * @package     Evozon_Blog
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Config
{

    /**
     * General post configuration: info about seo.
     */
    const XML_PATH_BLOG_GENERAL_SEO = 'evozon_blog_general/seo';

    /**
     * General post configuration: layout config
     */
    const XML_PATH_BLOG_GENERAL_LAYOUT = 'evozon_blog_general/layout';

    /**
     * General post configuration: datetime
     */
    const XML_PATH_BLOG_GENERAL_DATETIME = 'evozon_blog_general/datetime';

    /**
     * Post image placeholder configuration
     */
    const XML_PATH_BLOG_GENERAL_POST_IMAGE_PLACEHOLDER = 'evozon_blog_general/post_image_placeholders';

    /**
     * post listing configuration
     */
    const XML_PATH_BLOG_POST_LISTING = 'evozon_blog_post/listing';

    /**
     * post single page configuration
     */
    const XML_PATH_BLOG_POST_PAGE = 'evozon_blog_post/single_page';

    /**
     * recent posts configuration
     */
    const XML_PATH_BLOG_POST_RECENT = 'evozon_blog_post/recent';

    /**
     * related articles configuration 
     */
    const XML_PATH_BLOG_POST_RELATED = 'evozon_blog_post/related';

    /**
     * post archive configuration 
     */
    const XML_PATH_BLOG_POST_ARCHIVE = 'evozon_blog_post/archive';

    /**
     * post url configuration 
     */
    const XML_PATH_BLOG_POST_URL = 'evozon_blog_post/post_url';

    /**
     * post tags configuration 
     */
    const XML_PATH_BLOG_POST_TAGS = 'evozon_blog_post/tags';

    /**
     * post catalog related posts configuration 
     */
    const XML_PATH_BLOG_POST_CATALOG = 'evozon_blog_post/catalog';

    /**
     * post related products configuration 
     */
    const XML_PATH_BLOG_POST_PRODUCTS = 'evozon_blog_post/products';

    /**
     * post catalog categories related configuration 
     */
    const XML_PATH_BLOG_POST_CATALOG_CATEGORIES = 'evozon_blog_post/catalog_categories';

    /**
     * category related posts configuration 
     */
    const XML_PATH_BLOG_POST_FEATURED_POSTS_CATEGORY = 'evozon_blog_post/featured_posts_category';

    /**
     * rss feed configuration
     */
    const XML_PATH_BLOG_POST_RSS = 'evozon_blog_post/post_rss';

    /**
     * comments general configuration 
     */
    const XML_PATH_BLOG_COMMENT_GENERAL = 'evozon_blog_comment/general';

    /**
     * comments akismet spam configuration 
     */
    const XML_PATH_BLOG_COMMENT_SPAM_SERVICE = 'evozon_blog_comment/spam_service';

    /**
     * comments recent widget and block configuration 
     */
    const XML_PATH_BLOG_COMMENT_RECENT_COMMENTS_WIDGET = 'evozon_blog_comment/recent_comments_widget';

    /**
     * comments recent widget and block configuration 
     */
    const XML_PATH_BLOG_COMMENT_CUSTOMER_ACCOUNT_TAB = 'evozon_blog_comment/customer_account_tab';

    /**
     * toolbar and pagination configuration
     */
    const XML_PATH_BLOG_GENERAL_TOOLBAR = 'evozon_blog_general/toolbar_pagination';
    
    /**
     * settings to validate tags
     */
    const XML_PATH_BLOG_VALIDATION_TAGS = 'evozon_blog_validation/tags';
    
    /**
     * settings to validate comments
     */
    const XML_PATH_BLOG_VALIDATION_COMMENTS = 'evozon_blog_validation/comments';

//    const XML_PATH_BLOG_POST_LISTING_DISPLAY_CONTENT = 'evozon_blog_post/listing_display_content';

    /** Constants for the values defined in config.xml */

    /**
     * Path for the commets number from my blog comments list defined in config
     */
    const CONFIG_COMMENT_CUSTOMER_ACCOUNT_LIMIT = 'default/evozon_blog_comment/customer_account_tab/limit';

    /**
     * Path for maximum reply value defined in config
     */
    const CONFIG_SINGLE_PAGE_MAX_REPLY_LEVEL = 'default/evozon_blog_comment/general/max_allowed_reply_level';

    /**
     * Path for maximum reply value defined in config
     */
    const CONFIG_GALLERY_WIDGET_SLIDESHOW_AUTOSTART = 'default/evozon_blog_post/gallery_widget/slideshow_autostart';

    /**
     * local caching for configuration array.
     * 
     * @var Array
     */
    protected $_configData = [];

    /**
     * get all configuration array of the provided keys 
     * or just the value for the key provided. 
     * 
     * @param Array $pathKeys
     * @param String $key
     * @return String|Int|Array
     */
    protected function _getConfigData(Array $pathKeys = [], $key = NULL)
    {
        $configKey = md5(implode('/', $pathKeys));

        if (!array_key_exists($configKey,
                $this->_configData)) {
            $this->_configData[$configKey] = [];
            foreach ($pathKeys as $path) {
                if (($config = $this->getStoreConfig($path)) && is_array($config)) {
                    $this->_configData[$configKey] = array_merge(
                        $this->_configData[$configKey],
                        $config
                    );
                }
            }
        }

        return $key === NULL ? $this->_configData[$configKey] : $this->_configData[$configKey][$key];
    }

    /**
     * Get store config
     * 
     * @param string $path
     * @return string
     */
    protected function getStoreConfig($path)
    {
        return Mage::getStoreConfig($path, Mage::app()->getStore());
    }

    /**
     * return an array of configuration or a value of the key provided.
     * this is for all general configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getGeneralConfig($key = NULL)
    {
        $pathKeys = [
            self::XML_PATH_BLOG_GENERAL_SEO,
            self::XML_PATH_BLOG_GENERAL_LAYOUT,
            self::XML_PATH_BLOG_GENERAL_DATETIME,
            self::XML_PATH_BLOG_GENERAL_TOOLBAR,
            self::XML_PATH_BLOG_GENERAL_POST_IMAGE_PLACEHOLDER
        ];

        return $this->_getConfigData($pathKeys, $key);
    }

    /**
     * return an array of configuration or a value of the key provided.
     * this is for comments configuration.
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getCommentsConfig($key = NULL)
    {
        $pathKeys = array(
            self::XML_PATH_BLOG_COMMENT_GENERAL,
            self::XML_PATH_BLOG_GENERAL_DATETIME,
            self::XML_PATH_BLOG_COMMENT_RECENT_COMMENTS_WIDGET
        );

        return $this->_getConfigData($pathKeys, $key);
    }

    /**
     * get specific comments spam checker configuration.
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getCommentsSpamCheckerConfig($key = NULL)
    {
        return $this->_getConfigData([self::XML_PATH_BLOG_COMMENT_SPAM_SERVICE], $key);
    }

    /**
     * get post listing configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostListingConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_LISTING), $key);
    }

    /**
     * get post single page configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostSinglePageConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_PAGE), $key);
    }

    /**
     * get post recent posts configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostRecentConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_RECENT), $key);
    }

    /**
     * get post related posts configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostRelatedConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_RELATED), $key);
    }

    /**
     * get post archive configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostArchiveConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_ARCHIVE), $key);
    }

    /**
     * get post url configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostUrlConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_URL), $key);
    }

    /**
     * get post tags configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostTagsConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_TAGS), $key);
    }

    /**
     * get product related posts configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostCatalogConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_CATALOG), $key);
    }

    /**
     * get post related products configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostProductsConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_PRODUCTS), $key);
    }

    /**
     * get post related categories configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getPostCatalogCategoriesConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_CATALOG_CATEGORIES),
                $key);
    }

    /**
     * get category posts related configuration
     * 
     * @param String $key
     * @return String|Int|Array
     */
    public function getFeaturedPostsCategoryConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_FEATURED_POSTS_CATEGORY),
                $key);
    }

    /**
     * get rss feed configuration
     * 
     * @param String $key
     * @return Int|Array
     */
    public function getPostRssConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_POST_RSS), $key);
    }

    /**
     * get customer account comments tab configuration
     * 
     * @param String $key
     * @return String|Int
     */
    public function getCommentsCustomerAccountConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_COMMENT_CUSTOMER_ACCOUNT_TAB),
                $key);
    }

    /**
     * get toolbar and pagination configuration
     * 
     * @param string $key
     * @return string
     */
    public function getToolbarAndPaginationConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_GENERAL_TOOLBAR), $key);
    }
    
    /**
     * Get Validation config for Tags
     * 
     * @param string $key
     * @return string
     */
    public function getTagsValidationConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_VALIDATION_TAGS), $key);
    }
    
    /**
     * Get Validation config for Comments
     * 
     * @param string $key
     * @return string
     */
    public function getCommentsValidationConfig($key = NULL)
    {
        return $this->_getConfigData(array(self::XML_PATH_BLOG_VALIDATION_COMMENTS), $key);
    }
    
    /**
     * Getting the validation model
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $type
     * @return string
     */
    public function getValidationModel($type)
    {
        $function = 'get' . ucfirst($type) . 'ValidationConfig';
        $const = 'VALIDATION_' . strtoupper($type) . '_MODEL';

        return $this->$function(constant('Evozon_Blog_Model_Config_Validation::' . $const));
    }

    /**
     * Retrieve Attributes array used for sort by
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return array
     */
    public function getAttributesUsedForSortBy()
    {
        $usedForSortBy = array();
        $entityType = Evozon_Blog_Model_Post::ENTITY;
        $attributesData = Mage::getResourceModel('evozon_blog/attribute')->getAttributesUsedForSortBy();
        Mage::getSingleton('eav/config')->importAttributesData($entityType, $attributesData);
        
        foreach ($attributesData as $attributeData) {
            $attributeCode = $attributeData['attribute_code'];
            $usedForSortBy[$attributeCode] = Mage::getSingleton('eav/config')->getAttribute($entityType, $attributeCode);
        }
        
        return $usedForSortBy;
    }

    /**
     * Retrieve Attributes Used for Sort by as array
     * key = code, value = name
     *
     * @return array
     */
    public function getAttributeUsedForSortByArray()
    {
        $options = array();
        
        foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        }

        return $options;
    }

    /**
     * Return the customer account limit from config.xml
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return int
     */
    public static function getCommentCustomerAccountLimitConfig()
    {
        return (int) Mage::getConfig()->getNode(self::CONFIG_COMMENT_CUSTOMER_ACCOUNT_LIMIT);
    }

    /**
     * Return the comments max reply level from config.xml
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return int
     */
    public static function getCommentMaxReplyLevelConfig()
    {
        return (int) Mage::getConfig()->getNode(self::CONFIG_SINGLE_PAGE_MAX_REPLY_LEVEL);
    }

    /**
     * Return the gallery widget slideshow autostart from config.xml
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return int
     */
    public static function getGalleryWidgetSlideshowAutostartConfig()
    {
        return (int) Mage::getConfig()->getNode(self::CONFIG_GALLERY_WIDGET_SLIDESHOW_AUTOSTART);
    }

}
