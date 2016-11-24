<?php

/**
 * URL rewrite data using ArrayObject
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_Data_Array extends ArrayObject
    implements ArrayAccess
{
    /**
     * Class constructor
     * Builds an ArrayObject
     *
     * @throws Exception
     */
    public function __construct()
    {
        $data = Mage::getSingleton('evozon_blog/factory')->getRewriteResource()->getExistingDbRewrites();
        if (!is_array($data)) {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10200);
        }

        parent::__construct($data, self::ARRAY_AS_PROPS);
    }


    /**
     * Check if a created rewrite is valid within the database
     * If the rewrite has the same target path - it is valid (we should not re-create it)
     *
     * @param $requestPath
     * @param $targetPath
     * @return bool
     */
    public function isValidWithinDb($requestPath, $targetPath)
    {
        if (empty($this)) {
            return true;
        }

        if (!$this->offsetExists($requestPath)) {
            return true;
        }

        if ($this->offsetGet($requestPath) == $targetPath)
        {
            return true;
        }

        return false;
    }

    /**
     * Retrieve the matching elements stored in our data store
     * based on the regex pattern sent
     *
     * @param string $regex
     * @return RegexIterator
     */
    public function getMatchingElements($regex)
    {
        $iterator = new RegexIterator($this->getIterator(), "/{$regex}/i", RegexIterator::MATCH);
        return $iterator;
    }
}