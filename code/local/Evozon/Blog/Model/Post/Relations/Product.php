<?php
/**
 * Model for post-products relations
 *
 * @package     Evozon_Blog
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2016 Evozon
 * @link        http://www.evozon.com  Evozon
 */

class Evozon_Blog_Model_Post_Relations_Product extends Evozon_Blog_Model_Post_Relations_Abstract
{
    protected function getModelName()
    {
        return "evozon_blog/post_relations_product";
    }
    
    protected function getSelectedIdsDataName()
    {
        return 'related_products';
    }
    
     /**
     * Getting the post-product relations objects
     * The products should be filtered by: store, visiblity,status (enabled) and stock availability
     * All the attributes are required in order to fetch data for the catalog/product/list template
     *
     * @author  Dana Negrescu <dana.negrescu@evozon.com>
     * @param int $postId
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getCollectionByPostId($postId)
    {
        $productsRelationsCollection = Mage::getResourceModel('evozon_blog/post_relations_product_collection')
            ->addAttributeToFilter('entity_id', array('in' => $this->getIdsByPostId($postId)))
            ->addStoreFilter(Mage::app()->getStore()->getId())
            ->addAttributeToSelect(array('price', 'small_image', 'name', 'url_path'))
            ->addAttributeToSelect(array(
                'group_price', 'minimal_price', 'price_type',
                'price_view', 'special_price', 'tier_price'
                )
            )
            ->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE))
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        //getting just the products that are in stock (if it is not set in config not to display out of stock products)
        if (!Mage::getStoreConfig('cataloginventory/options/show_out_of_stock')) {
            $productsRelationsCollection->joinField(
                'is_in_stock', 
                'cataloginventory/stock_item',
                'is_in_stock',
                'product_id=entity_id',
                'is_in_stock=1',
                null,
                'left'
            );
        }

        if (Mage::getStoreConfig('catalog/seo/product_use_categories')) {
            $productsRelationsCollection->addRequestPath();
        }

        return $productsRelationsCollection;
    }

}