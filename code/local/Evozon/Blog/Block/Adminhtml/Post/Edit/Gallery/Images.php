<?php

/**
 * Images that will hold the templates and it will be used in FE to render the gallery widget created
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Gallery_Images extends Mage_Core_Block_Template
    implements Mage_Widget_Block_Interface
{

    /**
     * Sets the template if not specified
     * If there are no images specified (the parameter is empty), then we return an empty string
     * Because this widget instance was not invoked from a post content
     * Or no images have been selected
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _construct()
    {
        parent::_construct();
        
        $images = $this->getData('images');
        if (!empty($images)) {
            if ($this->getData('display')) {
                $this->setTemplate($this->getData('display'));
            }

            $this->setSlideshowAutostart($this->getData('slideshow_autostart'));
        }
    }

    /**
     * Return the post model from registry
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return \Evozon_Blog_Model_Post
     */
    protected function getPost()
    {
        return Mage::registry('blog_post');
    }

    /**
     * Return an array with the selected images
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return array
     */
    public function getSelectedImages()
    {
        $images = $this->getPost()->getGalleryImages($this->getData('images'), $this->getData('store'));

        return $images;
    }

    /**
     * Return the path to the resized image
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $imagePath
     * @param int $width
     * @param bool $keepFrame
     * @return string
     */
    public function resizeImage($imagePath, $width, $keepFrame = false)
    {
        return (string) Mage::helper('evozon_blog/post_image')->init($this->getPost(), 'image', $imagePath)->keepFrame($keepFrame)->resize($width);
    }

    /**
     * If there is only one image in the gallery, we do not need to display the controls and navigation for it
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com
     * @return boolean
     */
    public function getControlsAndNavigation()
    {
        if (count($this->getSelectedImages()) == 1) {
            return 0;
        }

        return 1;
    }

    /**
     * Setting individual widget instance id
     * 
     * @TODO find a better way to generate an UID for the gallery
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return string
     */
    public function getGalleryId()
    {
        return md5($this->getData('images') . rand(0, 99));
    }

    /**
     * Returns slideshow interval
     * In case the user did not insert a numeric value, it will be set the default
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return int
     */
    protected function getSlideshowInterval()
    {
        if (is_numeric($this->getData('slideshow_interval'))) {
            return $this->getData('slideshow_interval');
        }
       
        return Evozon_Blog_Model_Config::getGalleryWidgetSlideshowAutostartConfig();
    }
}
