<?php

/**
 * Image helper: useful function to manipulate images in different ways.
 *
 * @package     Evozon_Blog
 * @author      Andreea Macicasan <andreea.macicasan@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Helper_Post_Image extends Mage_Core_Helper_Abstract
{

    /**
     * Current model
     *
     * @var Mage_Catalog_Model_Product_Image
     */
    protected $_model;

    /**
     * Current Post
     *
     * @var Evozon_Blog_Model_Post
     */
    protected $_post;

    /**
     * Image File
     *
     * @var string
     */
    protected $_imageFile;

    /**
     * Image Placeholder
     *
     * @var string
     */
    protected $_placeholder;

    /**
     * Scheduled for resize image
     *
     * @var bool
     */
    protected $_scheduleResize = false;

    CONST PLACEHOLDER_PATH = 'images/evozon/blog/post/placeholder/';
    CONST PLACEHOLDER_FILE_TYPE = 'jpg';

    /**
     * Initialize helper to work with Image model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Post $post
     * @param string $attributeName
     * @param string $imageFile
     * @return Evozon_Blog_Helper_Post_Image
     */
    public function init(Evozon_Blog_Model_Post $post, $attributeName, $imageFile = null)
    {
        $this->_reset();
        $this->_setModel(Mage::getModel('evozon_blog/image'));
        $this->_getModel()->setDestinationSubdir($attributeName);
        $this->setPost($post);

        if ($imageFile) {
            $this->setImageFile($imageFile);

            return $this;
        }

        // add for work original size
        $this->_getModel()->setBaseFile($this->getPost()->getData($this->_getModel()->getDestinationSubdir()));

        return $this;
    }

    /**
     * Set current image model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Image $model
     * @return Evozon_Blog_Helper_Post_Image|Evozon_Blog_Helper_Post_Gallery
     */
    protected function _setModel($model)
    {
        $this->_model = $model;

        return $this;
    }

    /**
     * Get current Image model
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Model_Image
     */
    protected function _getModel()
    {
        return $this->_model;
    }

    /**
     * Set current post
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param Evozon_Blog_Model_Post $post
     * @return Evozon_Blog_Helper_Post_Image|Evozon_Blog_Helper_Post_Gallery
     */
    protected function setPost($post)
    {
        $this->_post = $post;

        return $this;
    }

    /**
     * Get current post
     *
     * @return Evozon_Blog_Model_Post
     */
    protected function getPost()
    {
        return $this->_post;
    }

    /**
     * Set Image file
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $file
     * @return Evozon_Blog_Helper_Post_Image|Evozon_Blog_Helper_Post_Gallery
     */
    protected function setImageFile($file)
    {
        $this->_imageFile = $file;

        return $this;
    }

    /**
     * Get Image file
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    protected function getImageFile()
    {
        return $this->_imageFile;
    }

    /**
     * Reset all previous data
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Helper_Post_Image|Evozon_Blog_Helper_Post_Gallery
     */
    protected function _reset()
    {
        $this->_model = null;
        $this->_post = null;
        $this->_imageFile = null;

        $this->_scheduleResize = false;

        return $this;
    }

    /**
     * Schedule resize of the image, $height can be null - in this case, 
     * lacking dimension will be calculated.
     * 
     * @see Evozon_Blog_Model_Image
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param int $width
     * @param int $height
     * @return Evozon_Blog_Helper_Post_Image|Evozon_Blog_Helper_Post_Gallery
     */
    public function resize($width, $height = null)
    {
        $this->_getModel()->setWidth($width)->setHeight($height);
        $this->_scheduleResize = true;

        return $this;
    }

    /**
     * Get placeholder
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function getPlaceholder()
    {
        if (!$this->_placeholder) {
            $attr = $this->_getModel()->getDestinationSubdir();
            $this->_placeholder = self::PLACEHOLDER_PATH . $attr . '.' . self::PLACEHOLDER_FILE_TYPE;
        }

        return $this->_placeholder;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     *
     * @see Evozon_Blog_Model_Image
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param bool $flag
     * @return Evozon_Blog_Helper_Post_Image
     */
    public function keepFrame($flag)
    {
        $this->_getModel()->setKeepFrame($flag);

        return $this;
    }

    /**
     * If the image file is defined the method will return the image file path
     * Else will return the image path after the attribute code
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    protected function getBaseFile()
    {
        if ($this->getImageFile()) {
            return $this->getImageFile();
        }

        return $this->getPost()->getData($this->_getModel()->getDestinationSubdir());
    }

    /**
     * Return image url
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function __toString()
    {
        try {
            $model = $this->_getModel();
            $model->setBaseFile($this->getBaseFile());

            if ($model->isCached()) {
                return $model->getUrl();
            }

            if ($this->_scheduleResize) {
                $model->resize();
            }

            $url = $model->saveFile()->getUrl();
        } catch (Exception $e) {
            $url = Mage::getDesign()->getSkinUrl($this->getPlaceholder());
        }

        return $url;
    }

    /**
     * Check - is this file an image
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $filePath
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function validateUploadFile($filePath)
    {
        if (!getimagesize($filePath)) {
            Mage::throwException($this->__('Disallowed file type.'));
        }

        $_processor = new Varien_Image($filePath);

        return $_processor->getMimeType() !== null;
    }

}
