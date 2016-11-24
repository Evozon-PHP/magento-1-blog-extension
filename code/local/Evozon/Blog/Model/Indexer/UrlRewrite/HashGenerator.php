<?php

/**
 * URL rewrite unique hash generator
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_HashGenerator
{
    /**
     * Hash delimiter used to create unique URLs
     * @var string
     */
    protected $_hashDelimiter = '-';

    /**
     * Data source for storing the existing URLs
     * @var Evozon_Blog_Model_Indexer_UrlRewrite_Data_Array
     */
    protected $_pathSource;


    /**
     * Evozon_Blog_Model_Indexer_UrlRewrite_HashGenerator constructor.
     */
    public function __construct(array $data)
    {
        if (!isset($data['path_source']))
        {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10200);
        }

        $this->setPathSource($data['path_source']);
    }

    /**
     * Get the next available hash for the set pattern.
     * This will return the next useable hash for the pattern based on store id
     *
     * @param string $pattern
     * @param null   $suffix
     * @param bool   $storeId
     * @param string $storeDelimiter
     *
     * @return string
     */
    public function getNextAvailableHash($pattern, $suffix = null, $storeId = false, $storeDelimiter = '##')
    {
        $hashedPattern = '';
        $finalHash     = '-1';
        $urlsFound     = array();
        $namePattern   = preg_quote($pattern, '/');
        $source = $this->getPathSource();

        //we add the suffix separately because it may not exist
        if (!is_null($suffix)) {
            $namePattern .= sprintf('(?:%s)?', preg_quote($suffix, '/'));
        }

        //retrieve the existing target_urls matching our pattern
        $urlsFound = $source->getMatchingElements($namePattern);

        if ($urlsFound instanceof RegexIterator && 0 !== count($urlsFound = iterator_to_array($urlsFound))) {
            //filter for only those urls matching our current store
            if (false !== $storeId) {
                $urlsFound = (array)preg_grep(sprintf('/%s%s/i', $storeDelimiter, $storeId), $urlsFound);
            }

            if (0 !== count($urlsFound) && is_array($urlsFound)) {
                $hashesFound = str_ireplace(
                    array($pattern, $suffix, $storeDelimiter . $storeId, $this->_hashDelimiter), '', $urlsFound
                );

                rsort($hashesFound, SORT_NUMERIC);

                //if we have a numeric hash and is greater than zero we increment it and return it
                //otherwise return an empty string
                $finalHash = is_array($hashesFound) && !empty($hashesFound[0]) && $hashesFound[0] > 0
                    ? ($this->_hashDelimiter . (int)++$hashesFound[0]) : $finalHash;
            }
        }

        $hashedPattern = sprintf('%s%s%s%s%s', $pattern, $finalHash, $suffix, $storeDelimiter, $storeId);

        //check if hash exists and if it doesn't we add it to the datasource
        //in order to know of its existance if we need to generate a new hash
        if (!$source->offsetExists($hashedPattern)) {
            $source->offsetSet($hashedPattern, $hashedPattern);
        }

        return (string)$finalHash;
    }

    /**
     * Set a data source
     * It must have all existing paths and hashes in the db
     *
     * @param Evozon_Blog_Model_Indexer_UrlRewrite_Data_Array $pathSource
     */
    public function setPathSource(Evozon_Blog_Model_Indexer_UrlRewrite_Data_Array $pathSource)
    {
        $this->_pathSource = $pathSource;
        return $this;
    }

    public function getPathSource()
    {
        return $this->_pathSource;
    }

    /**
     * Set the delimiter between the name and hash
     *
     * @param string $delimiter
     */
    public function setHashDelimiter($delimiter)
    {
        $this->_hashDelimiter = $delimiter;
        return $this;
    }
}