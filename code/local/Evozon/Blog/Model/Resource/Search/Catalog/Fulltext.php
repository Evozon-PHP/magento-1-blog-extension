<?php

/**
 * Class that will add extra functionalities to CatalogSearch
 * in order to manipulate the products collection result
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Search_Catalog_Fulltext extends Mage_CatalogSearch_Model_Resource_Fulltext
{

    /**
     * Attaches product ids to the existing products` search result
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $queryId
     * @param array $productIds
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function appendResult($queryId, $productIds)
    {
        $adapter = $this->_getWriteAdapter();

        try {
            foreach ($productIds as $productId) {
                $bind[] = array(
                    'query_id' => (int) $queryId,
                    'product_id' => (int) $productId
                );
            }

            if ($bind) {
                $adapter->insertOnDuplicate(
                    $this->getTable('catalogsearch/result'),
                    $bind,
                    array('product_id')
                );
            }
        } catch (Exception $exc) {
            throw $exc;
        }


        return $this;
    }

}
