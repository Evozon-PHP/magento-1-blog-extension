<?php

/**
 * Post helper form gallery content
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Block_Adminhtml_Post_Helper_Form_Gallery_Content extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    /**
     * Array with the allowed image types
     * 
     * @var array 
     */
    protected $_allowedImageTypes = array('*.gif', '*.jpg','*.jpeg', '*.png');
    
    /**
     * Constructor
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Return path to template
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function getTemplate()
    {
        return 'evozon/blog/post/helper/gallery.phtml';
    }
    
    /**
     * Return the array with the allowed image types
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return type
     */
    public function getAllowedImageTypes()
    {
        return $this->_allowedImageTypes;
    }
    
    /**
     * Add new image type
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param type $type
     */
    public function addAllowedImageType($type)
    {
        $this->_allowedImageTypes[] = $type;
        
        return $this;
    }
    
    /**
     * Prepare layout
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return Evozon_Blog_Block_Adminhtml_Post_Helper_Form_Gallery_Content
     */
    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        )
            ->_prepareUploader();
        
        Mage::dispatchEvent('evozon_blog_post_gallery_prepare_layout', array('block' => $this));
        
        return $this;
    }

    /**
     * @return mixed
     */
    private function _getMediaUploadUrl()
    {
        return Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/blog_media/upload');
    }


    /**
     *
     */
    protected function _prepareUploader()
    {
        $url = $this->_getMediaUploadUrl();
        $helper = Mage::helper('evozon_blog');

        if ($helper->isNoFlashUploader()) {
            // PATCH SUPEE-8788 or Magento 1.9.3
            $this->getUploader()->getUploaderConfig()
                ->setFileParameterName('image')
                ->setTarget($url);

            $browseConfig = $this->getUploader()->getButtonConfig();
            $browseConfig
                ->setAttributes(
                    array("accept"  =>  $browseConfig->getMimeTypesByExtensions('gif, png, jpeg, jpg'))
                );

        } else {
            $this->getUploader()->getConfig()
                ->setUrl($url)
                ->setFileField('image')
                ->setFilters(array(
                    'images' => array(
                        'label' => Mage::helper('evozon_blog')->__('Images (.gif, .jpg, .jpeg, .png)'),
                        'files' => $this->getAllowedImageTypes()
                    )
                ));
        }

    }
    
    /**
     * Return the images in json format
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @return string
     */
    public function getImagesJson()
    {
        if(is_array($this->getElement()->getValue())) {
            $value = $this->getElement()->getValue();
            if(count($value['images'])>0) {
                foreach ($value['images'] as &$image) {
                    $image['url'] = Mage::getSingleton('evozon_blog/post_media_config')
                        ->getMediaUrl($image['file']);
                }
                return Mage::helper('core')->jsonEncode($value['images']);
            }
        }
        return '[]';
    }
}
