<?php

/**
 * Image model
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2015 Evozon (http://www.evozon.com)
 * @author     Andreea Macicasan <andreea.macicasan@evozon.com>
 */
class Evozon_Blog_Model_Image extends Mage_Catalog_Model_Product_Image
{    
    /**
     * @var string 
     */
    protected $placeHolderFile = '';
    
    /**
     * @var string 
     */
    protected $placeHolderBaseDir = '';
    
    /**
     * Set placeholder file
     * 
     * @param string $file
     * @return \Evozon_Blog_Model_Image
     */
    public function setPlaceHolderFile($file)
    {
        $this->placeHolderFile = $file;
        
        return $this;
    }
    
    /**
     * Return placeholder file
     * 
     * @return type
     */
    public function getPlaceHolderFile()
    {
        return $this->placeHolderFile;        
    }
        
    /**
     * Set placeholder base dir
     * 
     * @param string $baseDir
     * @return \Evozon_Blog_Model_Image
     */
    public function setPlaceHolderBaseDir($baseDir)
    {
        $this->placeHolderBaseDir = $baseDir;
        
        return $this;
    }
    
    /**
     *  Return placeholder base dir
     * 
     * @return string
     */
    public function getPlaceHolderBaseDir()
    {
        return $this->placeHolderBaseDir;        
    }

    /**
     * Set filenames for base file and new file
     *
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param string $file
     * @return Evozon_Blog_Helper_Post_Image
     * @throws Exception
     */
    public function setBaseFile($file)
    {
        $this->_isBaseFilePlaceholder = false;

        if (($file) && (0 !== strpos($file, '/', 0))) {
            $file = '/' . $file;
        }

        $baseDir = Mage::getSingleton('evozon_blog/post_media_config')->getBaseMediaPath();
        
        try {
            if ('/no_selection' == $file ||
                (!$this->_fileExists($baseDir . $file) || !$this->_checkMemory($baseDir . $file))
            ) {
                $this->preprocessPlaceHolder($baseDir);
                
                // set the file and the dir from placeholder data
                $file = $this->getPlaceHolderFile();
                $baseDir = $this->getPlaceHolderBaseDir();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }                
        
        $this->_baseFile = $baseDir . $file;
        $path = array(
            Mage::getSingleton('evozon_blog/post_media_config')->getBaseMediaPath(),
            'cache',
            Mage::app()->getStore()->getId(),
            $path[] = $this->getDestinationSubdir()
        );
        
        if((!empty($this->_width)) || (!empty($this->_height)))
            $path[] = "{$this->_width}x{$this->_height}";

        $this->_newFile = implode('/', $path) . $file;

        return $this;
    }
  
    /**
     * Return the placeholder data (file and base dir)
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $baseDir
     * @return string
     */
    protected function getPlaceholderData($baseDir)
    {
        $placeholderData = array();
        
        $configDefaultImage = Mage::getStoreConfig(
            Evozon_Blog_Model_Config::XML_PATH_BLOG_GENERAL_POST_IMAGE_PLACEHOLDER . "/{$this->getDestinationSubdir()}_placeholder", 
            Mage::app()->getStore()
        );
        $configPlaceholder = '/placeholder/' . $configDefaultImage;
        
        if ($configDefaultImage && $this->_fileExists($baseDir . $configPlaceholder)) {
            $placeholderData['file'] = $configPlaceholder;
            
            return $placeholderData;
        } 
        
        // replace file with skin or default skin placeholder
        $baseDir = Mage::getDesign()->getSkinBaseDir();
        $skinPlaceholder = "/images/evozon/blog/post/placeholder/{$this->getDestinationSubdir()}.jpg";
        $placeholderData['file'] = $skinPlaceholder;

        if (!file_exists($baseDir . $placeholderData['file'])) {
            $placeholderData['dir'] = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
            if (!file_exists($baseDir . $placeholderData['file'])) {
                $placeholderData['dir'] = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => 'base'));
            }
        }
        
        return $placeholderData;
    }

    /**
     * Set the placeholder file and dir if the file is null
     * 
     * @param string $baseDir
     * @return string
     * @throws Exception
     */
    protected function preprocessPlaceHolder($baseDir)
    {
        // get the placeholder file and base dir
        $placeholderData = $this->getPlaceholderData($baseDir);

        // set the values
        $file = (isset($placeholderData['file']) ? $placeholderData['file'] : '');
        $baseDir = (isset($placeholderData['dir']) ? $placeholderData['dir'] : $baseDir);

        $this->_isBaseFilePlaceholder = true;

        $this
            ->setPlaceHolderFile($file)
            ->setPlaceHolderBaseDir($baseDir);                
                
        if ((!$file) || (!file_exists($baseDir . $file))) {
            throw new Exception(Mage::helper('evozon_blog')->__('Image file was not found.'));
        }

        return $this;
    }
}
