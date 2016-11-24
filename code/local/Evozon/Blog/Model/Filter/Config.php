<?php

/**
 * Evozon Blog Validation config class
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Filter_Config extends Varien_Simplexml_Config
{

    /**
     * Validation configuration file
     */
    const CONFIG_FILE = 'validation.xml';

    /**
     * XML Config node for validation
     */
    const XML_PATH_BLOG_VALIDATION_NODE = 'validation';

    /**
     * XML Config path name for comments validation
     */
    const XML_PATH_BLOG_VALIDATION_TYPE_COMMENTS = 'comments';

    /**
     * XML Config path name for tags validation
     */
    const XML_PATH_BLOG_VALIDATION_TYPE_TAGS = 'tags';

    /**
     * Constructor
     *
     * @param null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $this->setXml(
            Mage::getConfig()
                ->loadModulesConfiguration(self::CONFIG_FILE)
                ->getNode()
        );
    }

    /**
     * Get data from child nodes of the tags/comments
     *
     * @param string $type
     *
     * @return array|string
     * @throws Mage_Core_Exception
     */
    public function getDataByValidatorType($type)
    {
        $node = $this->getNode(self::XML_PATH_BLOG_VALIDATION_NODE . '/' . $type);
        if (!$node) {
            Mage::throwException(sprintf('Missing node name %s in config file.', $type));
        }

        return $node->asArray();
    }
}
