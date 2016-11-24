<?php
/**
 * Collection used to add new features for the product collections
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Model_Resource_Post_Relations_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Fetches and selects the product category url for each product that belongs to a category
     *
     * @author Dana Negrescu <dana.negrescu@evozon.com>
     * @return \Mage_Catalog_Model_Resource_Product_Collection
     */
    public function addRequestPath()
    {
        $ids = $this->getAllIds();
        if (empty($ids)) {
            return $this;
        }

        $productCategoryRelations = Mage::getResourceSingleton('evozon_blog/post_relations_product')
            ->getProductAndCategoriesRelations($ids);

        $urlRewriteHelper = $this->_factory->getProductUrlRewriteHelper();
        foreach ($this->getItems() as $item) {
            $productId = $item->getId();
            if (isset($productCategoryRelations[$productId])) {
                //getting the url rewrite helper and fetching the rows that return our url to the category
                $url = $this->getConnection()->fetchRow(
                    $urlRewriteHelper->getTableSelect((array) $productId, $productCategoryRelations[$productId],
                        Mage::app()->getStore()->getId()
                    )
                );
                $item->setData('request_path', $url['request_path']);
            }
        }

        return $this;
    }
}