<?php

/**
 * Post media gallery attribute backend model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Post_Attribute_Backend_Media extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    /**
     * Return resource model
     *
     * @return Evozon_Blog_Model_Resource_Post_Attribute_Backend_Media
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getResource()
    {        
        return Mage::getResourceSingleton('evozon_blog/post_attribute_backend_media');
    }
    
    /**
     * Return media config
     *
     * @return Evozon_Blog_Model_Post_Media_Config
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('evozon_blog/post_media_config');
    }
    
    /**
     * Load attribute data after post loaded
     *
     * @param Evozon_Blog_Model_Post $object
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function afterLoad($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = array();
        $value['images'] = array();
        $value['values'] = array();
        $localAttributes = array('label', 'href', 'position', 'disabled');
        
        $images = $this->_getResource()->loadGallery($object, $this);
        foreach ($images as $image) {
            foreach ($localAttributes as $localAttribute) {
                if (is_null($image[$localAttribute])) {
                    $image[$localAttribute] = $this->_getDefaultValue($localAttribute, $image);
                }
            }
            $value['images'][] = $image;
        }

        $object->setData($attrCode, $value);
    }
    
    /**
     * Before save method
     * 
     * @param Evozon_Blog_Model_Post $object
     * @return \Evozon_Blog_Model_Post_Attribute_Backend_Media
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function beforeSave($object)
    {        
        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode); 
        if (!is_array($value) || !isset($value['images'])) {
            return;
        }

        if(!is_array($value['images']) && strlen($value['images']) > 0) {
           $value['images'] = Mage::helper('core')->jsonDecode($value['images']);
        }

        if (!is_array($value['images'])) {
           $value['images'] = array();
        }

        $clearImages = array();
        $newImages   = array();
        $existImages = array();
        
        if ($object->getIsDuplicate()!= true) {
            foreach ($value['images'] as &$image) {
                if(!empty($image['removed'])) {
                    $clearImages[] = $image['file'];
                } 
                
                if (!isset($image['value_id'])) {
                    $newFile                   = $this->_moveImageFromTmp($image['file']);
                    $image['new_file'] = $newFile;
                    $newImages[$image['file']] = $image;
                    $this->_renamedImages[$image['file']] = $newFile;
                    $image['file']             = $newFile;
                } else {
                    $existImages[$image['file']] = $image;
                }
            }
        } else {
            // For duplicating we need copy original images.
            $duplicate = array();
            foreach ($value['images'] as &$image) {
                if (!isset($image['value_id'])) {
                    continue;
                }
                $newFile = $this->_copyImage($image['file']);
                $newImages[$image['file']] = array(
                    'new_file' => $newFile,
                    'label' => $image['label']
                );
                $duplicate[$image['value_id']] = $newFile;
            }

            $value['duplicate'] = $duplicate;
        }

        foreach ($object->getMediaAttributes() as $mediaAttribute) {
            $mediaAttrCode = $mediaAttribute->getAttributeCode();
            $attrData = $object->getData($mediaAttrCode);
            if (in_array($attrData, $clearImages)) {
                $object->setData($mediaAttrCode, 'no_selection');
            }
            if (in_array($attrData, array_keys($newImages))) {
                $object->setData($mediaAttrCode, $newImages[$attrData]['new_file']);
                $object->setData($mediaAttrCode.'_label', $newImages[$attrData]['label']);
            }

            if (in_array($attrData, array_keys($existImages))) {
                $object->setData($mediaAttrCode.'_label', $existImages[$attrData]['label']);
            }
        }
        
        Mage::dispatchEvent('evozon_blog_post_media_save_before', array('post' => $object, 'images' => $value));

        $object->setData($attrCode, $value);

        return $this;
    }
    
    /**
     * After save method
     * 
     * @param Evozon_Blog_Model_Post $object
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function afterSave($object)
    {
        if ($object->getIsDuplicate() == true) {
            $this->duplicate($object);
            return;
        }

        $attrCode = $this->getAttribute()->getAttributeCode();
        $value = $object->getData($attrCode);
        if (!is_array($value) || !isset($value['images']) || $object->isLockedAttribute($attrCode)) {
            return;
        }

        $storeId = $object->getStoreId();

        $storeIds = $object->getStoreIds();
        $storeIds[] = Mage_Core_Model_App::ADMIN_STORE_ID;

        // remove current storeId
        $storeIds = array_flip($storeIds);
        unset($storeIds[$storeId]);
        $storeIds = array_keys($storeIds);

        $images = Mage::getResourceModel('evozon_blog/post')
            ->getAssignedImages($object, $storeIds);

        $picturesInOtherStores = array();
        foreach ($images as $image) {
            $picturesInOtherStores[$image['filepath']] = true;
        }

        $toDelete = array();
        $filesToValueIds = array();
        foreach ($value['images'] as &$image) {
            if(!empty($image['removed'])) {
                if(isset($image['value_id']) && !isset($picturesInOtherStores[$image['file']])) {
                    $toDelete[] = $image['value_id'];
                }
                continue;
            }

            if(!isset($image['value_id'])) {
                $data = array();
                $data['entity_id']      = $object->getId();
                $data['attribute_id']   = $this->getAttribute()->getId();
                $data['value']          = $image['file'];
                $image['value_id']      = $this->_getResource()->insertGallery($data);
            }

            $this->_getResource()->deleteGalleryValueInStore($image['value_id'], $object->getStoreId());
            
            // Add per store labels, position, disabled
            $data = array();
            $data['value_id'] = $image['value_id'];
            $data['label']    = $image['label'];
            $data['href']    = $image['href'];
            $data['position'] = (int) $image['position'];
            $data['disabled'] = (bool) $image['disabled'];
            $data['store_id'] = (int) $object->getStoreId();

            $this->_getResource()->insertGalleryValueInStore($data);
        }

        $this->_getResource()->deleteGallery($toDelete);
    }
    
    /**
     * Return the unique name of the file
     *
     * @param string $file
     * @param string $dirsep
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getUniqueFileName($file, $dirsep) 
    {
        if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
            $destFile = Mage::helper('core/file_storage_database')
                ->getUniqueFilename(
                    Mage::getSingleton('evozon_blog/post_media_config')->getBaseMediaUrlAddition(),
                    $file
                );
        } else {
            $destFile = dirname($file) . $dirsep
                . Mage_Core_Model_File_Uploader::getNewFileName($this->_getConfig()->getMediaPath($file));
        }

        return $destFile;
    }
    
    /**
     * Return default value if isset else return empty string
     * 
     * @param string $key
     * @param string $image
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getDefaultValue($key, &$image)
    {
        if (isset($image[$key . '_default'])) {
            return $image[$key . '_default'];
        }

        return '';
    }
    
    /**
     * Validate post_media attribute data
     *
     * @param Evozon_Blog_Model_Post $object
     * @throws Mage_Core_Exception
     * @return bool
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function validate($object)
    {
        if ($this->getAttribute()->getIsRequired()) {
            $value = $object->getData($this->getAttribute()->getAttributeCode());
            if ($this->getAttribute()->isValueEmpty($value)) {
                if ( !(is_array($value) && count($value) > 0) ) {
                    return false;
                }
            }
        }
        if ($this->getAttribute()->getIsUnique()) {
            if (!$this->getAttribute()->getEntity()->checkAttributeUniqueValue($this->getAttribute(), $object)) {
                $label = $this->getAttribute()->getFrontend()->getLabel();
                Mage::throwException(Mage::helper('evozon_blog')->__('The value of attribute "%s" must be unique.', $label));
            }
        }

        return true;
    }
    
    /**
     * Move image from temporary directory
     *
     * @param string $file
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _moveImageFromTmp($file)
    {
        $ioObject = new Varien_Io_File();
        $destDirectory = dirname($this->_getConfig()->getMediaPath($file));
        try {
            $ioObject->open(array('path'=>$destDirectory));
        } catch (Exception $e) {
            $ioObject->mkdir($destDirectory, 0777, true);
            $ioObject->open(array('path'=>$destDirectory));
        }

        if (strrpos($file, '.tmp') == strlen($file)-4) {
            $file = substr($file, 0, strlen($file)-4);
        }
        $destFile = $this->_getUniqueFileName($file, $ioObject->dirsep());

        /** @var $storageHelper Mage_Core_Helper_File_Storage_Database */
        $storageHelper = Mage::helper('core/file_storage_database');

        if ($storageHelper->checkDbUsage()) {
            $storageHelper->renameFile(
                $this->_getConfig()->getTmpMediaShortUrl($file),
                $this->_getConfig()->getMediaShortUrl($destFile));

            $ioObject->rm($this->_getConfig()->getTmpMediaPath($file));
            $ioObject->rm($this->_getConfig()->getMediaPath($destFile));
        } else {
            $ioObject->mv(
                $this->_getConfig()->getTmpMediaPath($file),
                $this->_getConfig()->getMediaPath($destFile)
            );
        }

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }
    
    /**
     * Copy image and return new filename created
     *
     * @param string $file
     * @return string
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _copyImage($file)
    {
        try {
            $ioObject = new Varien_Io_File();
            $destDirectory = dirname($this->_getConfig()->getMediaPath($file));
            $ioObject->open(array('path'=>$destDirectory));

            $destFile = $this->_getUniqueFileName($file, $ioObject->dirsep());

            if (!$ioObject->fileExists($this->_getConfig()->getMediaPath($file),true)) {
                throw new Exception();
            }

            if (Mage::helper('core/file_storage_database')->checkDbUsage()) {
                Mage::helper('core/file_storage_database')
                    ->copyFile($this->_getConfig()->getMediaShortUrl($file),
                               $this->_getConfig()->getMediaShortUrl($destFile));

                $ioObject->rm($this->_getConfig()->getMediaPath($destFile));
            } else {
                $ioObject->cp(
                    $this->_getConfig()->getMediaPath($file),
                    $this->_getConfig()->getMediaPath($destFile)
                );
            }

        } catch (Exception $e) {
            $file = $this->_getConfig()->getMediaPath($file);
            Mage::throwException(
                Mage::helper('evozon_blog')->__('Failed to copy file %s. Please, delete media with non-existing images and try again.', $file)
            );
        }

        return str_replace($ioObject->dirsep(), '/', $destFile);
    }
    
    /**
     * Duplicate image
     * 
     * @param type $object
     * @return \Evozon_Blog_Model_Post_Attribute_Backend_Media
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function duplicate($object)
    {
        $attrCode = $this->getAttribute()->getAttributeCode();
        $mediaGalleryData = $object->getData($attrCode);

        if (!isset($mediaGalleryData['images']) || !is_array($mediaGalleryData['images'])) {
            return $this;
        }

        $this->_getResource()->duplicate(
            $this,
            (isset($mediaGalleryData['duplicate']) ? $mediaGalleryData['duplicate'] : array()),
            $object->getOriginalId(),
            $object->getId()
        );

        return $this;
    }

    /**
     * Return the not duplicates files from media directories
     *
     * @param String $fileName
     * @param String $dispretionPath
     * @return String
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    protected function _getNotDuplicatedFilename($fileName, $dispretionPath)
    {
        $fileMediaName = $dispretionPath . DS
                  . Mage_Core_Model_File_Uploader::getNewFileName($this->_getConfig()->getMediaPath($fileName));
        $fileTmpMediaName = $dispretionPath . DS
                  . Mage_Core_Model_File_Uploader::getNewFileName($this->_getConfig()->getTmpMediaPath($fileName));

        if ($fileMediaName != $fileTmpMediaName) {
            if ($fileMediaName != $fileName) {
                return $this->_getNotDuplicatedFileName($fileMediaName, $dispretionPath);
            } 
            
            if ($fileTmpMediaName != $fileName) {
                return $this->_getNotDuplicatedFilename($fileTmpMediaName, $dispretionPath);
            }
        }

        return $fileMediaName;
    }
}
