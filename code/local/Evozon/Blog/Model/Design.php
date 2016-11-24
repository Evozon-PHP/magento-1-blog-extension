<?php

/**
 * Design Model for the post view 
 * Sets the custom design theme 
 * 
 * @category   Evozon
 * @package    Evozon_Blog
 * @copyright  Copyright (c) 2016 Evozon (http://www.evozon.com)
 * @author     Dana Negrescu <dana.negrescu@evozon.com>
 */
class Evozon_Blog_Model_Design extends Mage_Core_Model_Abstract
{

    /**
     * Working object
     * @var Evozon_Blog_Model_Post 
     */
    protected $_post;

    /**
     * Desing settings
     * @var Varien_Object 
     */
    protected $_settings;

    /**
     * Construct and get custom design settings
     * Applies custom design theme if it has been set
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param Evozon_Blog_Model_Post $object
     * @return Varien_Object
     * @throws Evozon_Blog_Model_Exception_Validate
     */
    public function __construct($object)
    {
        $object = array_pop($object);
        if ($object instanceof Evozon_Blog_Model_Post) {
            $this->_post = $object;

            return $this;
        }

        throw new Evozon_Blog_Model_Exception_Validate("Evozon_Blog_Model_Post object is required.");
    }

    /**
     * Apply package and theme
     *
     * @param string $package
     * @param string $theme
     */
    protected function _apply($package, $theme)
    {
        Mage::getSingleton('core/design_package')
            ->setPackageName($package)
            ->setTheme($theme);
    }

    /**
     * Apply custom design
     */
    public function applyCustomDesign()
    {
        $design = $this->_settings->getCustomDesign();
        if (!$design) {
            return false;
        }

        $designInfo = explode('/', $design);
        if (count($designInfo) != 2) {
            return false;
        }

        $package = $designInfo[0];
        $theme = $designInfo[1];

        $this->_apply($package, $theme);
    }

    /**
     * Get custom layout settings
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return Varien_Object
     */
    public function getCustomDesignSettings()
    {
        if ($this->_settings) {
            return $this->_settings;
        }
        
        $settings = new Varien_Object;
        if (!$this->_post) {
            return $settings;
        }

        $this->_settings = $settings->setCustomDesign($this->_post->getCustomDesign())
            ->setPageLayout($this->_post->getPageLayout())
            ->setLayoutUpdates((array) $this->_post->getCustomLayoutUpdate());

        return $this->_settings;
    }

}
