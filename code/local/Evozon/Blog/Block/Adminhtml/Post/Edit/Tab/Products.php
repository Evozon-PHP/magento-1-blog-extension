<?php

/**
 * Load the product so that blog posts can be assigned to certain products.
 *
 * @package     Evozon_Blog
 * @author      Tiberiu Contiu <tiberiu.contiu@evozon.com>
 * @author      Dana Negrescu <dana.negrescu@evozon.com>
 * @copyright   Copyright (c) 2015, Evozon
 * @link        http://www.evozon.com  Evozon
 */
class Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set defaults
     */
    public function __construct()
    {
        parent::__construct();

        // set defaults
        $this->setId('product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);

        // if post is being edited
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(array('in_products' => 1));
        }
    }

    /**
     * Prepare collection of products and add the left join with relation table
     *
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Products
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareCollection()
    {
        // load product collection and add basic joins.
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addWebsiteFilter($this->getPost()->getWebsiteIds());
        $collection->addAttributeToSelect('price');

        $collection->joinAttribute('product_name', 'catalog_product/name', 'entity_id', null, 'left', Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');

        // add join with catalog inventory for qty field
        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Very important: exclude mass actions as it will conflict with form save
     *
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Products
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * Prepare product grid columns
     *
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Products
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _prepareColumns()
    {
        // the checkbox column which will allow to see/check/uncheck selected products
        $this->addColumn('in_products', array(
            'header_css_class' => 'a-center',
            'type' => 'checkbox',
            'name' => 'in_products',
            'values' => $this->_getSelectedRelatedProducts(),
            'align' => 'center',
            'index' => 'entity_id'
        ));

        // name of the product
        $this->addColumn('product_name', array(
            'header' => Mage::helper('evozon_blog')->__('Name'),
            'align' => 'left',
            'index' => 'product_name',
            'width' => '400px',
        ));

        // add the sku
        $this->addColumn('sku', array(
            'header' => Mage::helper('evozon_blog')->__('SKU'),
            'align' => 'left',
            'index' => 'sku',
            'width' => '200px',
        ));

        // show visibility
        $this->addColumn('visibility', array(
            'header' => Mage::helper('evozon_blog')->__('Visibility'),
            'width' => '100px',
            'index' => 'visibility',
            'type' => 'options',
            'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        // product price
        $this->addColumn('price', array(
            'header' => Mage::helper('evozon_blog')->__('Price'),
            'type' => 'currency',
            'width' => '1',
            'currency_code' => (string)Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index' => 'price'
        ));

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $this->addColumn('qty', array(
                'header' => Mage::helper('evozon_blog')->__('Qty'),
                'width' => '100px',
                'type' => 'number',
                'index' => 'qty',
            ));
        }

        return $this;
    }


    /**
     * Get products that has been previously selected and saved to relation table
     *
     * @return array Products that has been selected
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _getSelectedRelatedProducts()
    {
        $products = $this->getRelatedProducts();

        if (!is_array($products)) {
            $products = $this->getSelectedProductsFromGrid();
        }

        return $products;
    }

    /**
     * Getting data and keeping it set for further save
     * 
     * @return array
     */
    public function getSelectedProductsFromGrid()
    {
        $products = (array) $this->getPost()->getSelectedRelatedProducts();

        return $products;
    }

    /**
     * Make sure click on row goes nowhere
     *
     * @param Mage_Catalog_Model_Product $item
     * @return string
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * Get the grid through ajax from following url
     *
     * @return String
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/blog_post/productsGrid', array(
                'id' => $this->getPost()->getId(), '_current' => true
        ));
    }

    /**
     * Return post model registered in controller
     *
     * @return \Evozon_Blog_Model_Post
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    public function getPost()
    {
        return Mage::registry('evozon_blog');
    }

    /**
     * Overwrite base method.
     *
     * @param Mage_Catalog_Model_Product $column
     * @return \Evozon_Blog_Block_Adminhtml_Post_Edit_Tab_Products
     * @author Tiberiu Contiu <tiberiu.contiu@evozon.com>
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedRelatedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }

        return $this;
    }
}
