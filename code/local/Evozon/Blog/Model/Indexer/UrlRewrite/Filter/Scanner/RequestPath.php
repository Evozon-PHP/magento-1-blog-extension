<?php

/**
 * Unique request_path scanner and resolver for url rewrites
 *
 * @package     Evozon_Blog
 * @author      Denis Rendler <denis.rendler@evozon.com>
 * @copyright   Copyright (c) 2015 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Scanner_RequestPath
    implements Evozon_Blog_Model_Indexer_UrlRewrite_Filter_Interface_Scanner
{

    /**
     * Stores product suffixes
     * @var array
     */
    protected $_storeSuffixes = array();

    /**
     * Hash Generator
     */
    protected $_hashGenerator = null;

    /**
     * Data source that contains all existing paths array
     *
     * @var Evozon_Blog_Model_Indexer_Urlrewrite_Data_Array
     */
    protected $_pathArray = null;

    /**
     * {@inheritdoc}
     * @param array|mixed $rewriteData
     *
     * @return array|bool
     */
    public function validate($rewriteData)
    {
        $duplicates   = array();
        $scanned      = array();
        $storeSuffix  = $this->getStoreSuffixes();
        $source = $this->getPathsArray();

        foreach($rewriteData as $key => $rewrite) {
            //generate a unique identifier based on id_path and store_id
            //so that we don't mix paths between stores
            $suffix  = $storeSuffix[$rewrite['store_id']];
            $reqPath = $rewrite['request_path'] . '##' . $rewrite['store_id'];

            //we add the request_path to our scanned array the first time
            //the rest we add as duplicates to be resolved
            if (!array_key_exists($reqPath, $scanned) && $source->isValidWithinDb($reqPath, $rewrite['target_path']) === true) {
                $scanned[$reqPath] = $key;
                continue;
            }
            //init the $duplicate counter for this request_path
            if (!in_array($reqPath, array_keys($duplicates))) {
                $duplicates[$reqPath] = array();
            }

            do {
                $hash = $this->getHashGenerator()
                    ->getNextAvailableHash(
                        str_ireplace($suffix, '', $rewrite['request_path']),
                        $suffix,
                        $rewrite['store_id']
                    );
            } while(in_array($hash, $duplicates[$reqPath]));

            $duplicates[$reqPath][$hash] = $key;
        }

        return !empty($duplicates) ? $duplicates : true;
    }

    /**
     * Resolve the unique request_path requirement by attaching a hash value at the end
     *
     * @param array|mixed $issues
     * @param array|mixed $rewriteData
     *
     * @return mixed|void
     * @throws Exception
     */
    public function resolve($issues, $rewriteData)
    {
        $suffix = $this->getStoreSuffixes();
        //add the generated hash to the rewrite's target_path
        $result = array_walk_recursive($issues,
            function($rewriteKey, $counter) use ($rewriteData, $suffix) {
                $rewrite = $rewriteData->getItemById($rewriteKey);

                //remove this rewrite if it has no data because it may be corrupt
                //and crash the system
                if (!$rewrite->hasData()) {
                    $rewriteData->removeItemByKey($rewriteKey);
                }

                $storeId = $rewrite->getStoreId();
                $reqPath = $rewrite->getRequestPath();
                if (false !== ($position = strripos($reqPath, $suffix[$storeId]))) {
                    $reqPath = substr($reqPath, 0, $position);
                    $counter .= $suffix[$storeId];
                }

                $rewrite->setRequestPath($reqPath . $counter);

                if ($rewrite->hasIdentifier()) {
                    $rewrite->setIdentifier($rewrite->getRequestPath());
                }
        });

        if (false === $result) {
            throw Evozon_Blog_Model_Exception_IndexFactory::instance(10100);
        }
    }

    /**
     * Creating an array with store suffixes
     *
     * @return array
     */
    public function getStoreSuffixes()
    {
        if (empty($this->_storeSuffixes))
        {
            $suffix = array();
            $storeIds = Mage::app()->getStores();
            array_walk($storeIds, function (&$store) {
                $store = $store instanceof Mage_Core_Model_Store ? $store->getId() : -1;
            });

            foreach ($storeIds as $storeId) {
                $storeSuffix = Mage::helper('catalog/product')
                    ->getProductUrlSuffix($storeId);
                $suffix[$storeId] = (!empty($storeSuffix) && strpos($storeSuffix, '.') === false)
                    ? '.' . $storeSuffix
                    : $storeSuffix;
            }

            $this->_storeSuffixes = $suffix;
        }

        return $this->_storeSuffixes;
    }

    /**
     * Array to contain all existing request_paths in the db
     * upon which the scanner will validate the paths and resolve the duplicates
     *
     * @return Evozon_Blog_Model_Indexer_UrlRewrite_Data_Array
     */
    public function getPathsArray()
    {
        if (is_null($this->_pathArray))
        {
            $this->_pathArray = Mage::getModel('evozon_blog/indexer_urlRewrite_data_array');
        }

        return $this->_pathArray;
    }

    public function getHashGenerator()
    {
        if (is_null($this->_hashGenerator)) {
            $this->_hashGenerator = Mage::getModel('evozon_blog/indexer_urlRewrite_hashGenerator', array('path_source' => $this->getPathsArray()));
        }

        return $this->_hashGenerator;
    }
}