<?php

/**
 * Tag Collection
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Tag_Collection extends Mage_Catalog_Model_Resource_Collection_Abstract
{

    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'evozon_blog_tags';

    /**
     * Post to website linkage table
     *
     * @var string
     */
    protected $_postTagsTable;

    /**
     * Init collection of post objects
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('evozon_blog/tag');
    }
   
    /**
     * Filter tags (name attribute) according to specific input and store view;
     * There will be done a LIKE search (input%, %input, %input%) and the search results
     * will be united
     * 
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param string $input
     * @return \Evozon_Blog_Model_Resource_Tag_Collection
     */
    public function filterByInput($input)
    {
        $this->addAttributeToFilter(
            array(
                array('attribute' => 'name', 'like' => '%' . $input),
                array('attribute' => 'name', 'like' => '%' . $input . '%'),
                array('attribute' => 'name', 'like' => $input . '%')
        ));

        return $this;
    }
    
    /**
     * Set the locale dates for tags collection
     * 
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     */
    public function setProperDateFormat()
    {
        if (Mage::app()->getStore()->isAdmin()) {
            foreach ($this->getItems() as $tag) {
                $tag->setCreatedAt($this->getLocaleDate($tag->getCreatedAt()));
            }

            return $this;
        }

        return $this;
    }
    
    /**
     * Return the date converted in the locale date
     * 
     * @TODO   Define this method only in one place (is defined in post, comment and tag collection)
     * @author Andreea Macicasan <andreea.macicasan@evozon.com>
     * @param  string $date
     * @return string
     */
    protected function getLocaleDate($date) {
        return Mage::helper('evozon_blog')->getLocaleDate($date);
    }
}
