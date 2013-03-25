<?php

/**
 * Shopping Cart Gift Rule Products Grid
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit_Tab_Products_Grid extends
    Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('products_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setDefaultFilter(array('in_products' => 1));
    }
    
    /**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Related
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
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
    
    /**
     * Prepare collection for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect(
            '*');
        
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }
    
    /**
     * Define grid columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn('in_products',
            array('header_css_class' => 'a-center',
                'type' => 'checkbox', 'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'align' => 'center', 'index' => 'entity_id'
            ));
        
        $this->addColumn('entity_id',
            array('header' => Mage::helper('catalog')->__('ID'),
                'sortable' => true, 'width' => 60, 'index' => 'entity_id'
            ));
        
        $this->addColumn('name',
            array('header' => Mage::helper('catalog')->__('Name'),
                'index' => 'name'
            ));
        
        $this->addColumn('type',
            array('header' => Mage::helper('catalog')->__('Type'),
                'width' => 100, 'index' => 'type_id', 'type' => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')->setEntityTypeFilter(
            Mage::getModel('catalog/product')->getResource()->getTypeId())->load()->toOptionHash();
        
        $this->addColumn('set_name',
            array(
                'header' => Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => 130, 'index' => 'attribute_set_id',
                'type' => 'options', 'options' => $sets,
            ));
        
        $this->addColumn('status',
            array('header' => Mage::helper('catalog')->__('Status'),
                'width' => 90, 'index' => 'status', 'type' => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));
        
        $this->addColumn('visibility',
            array('header' => Mage::helper('catalog')->__('Visibility'),
                'width' => 90, 'index' => 'visibility',
                'type' => 'options',
                'options' => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
            ));
        
        $this->addColumn('sku',
            array('header' => Mage::helper('catalog')->__('SKU'),
                'width' => 80, 'index' => 'sku'
            ));
        
        $this->addColumn('price',
            array('header' => Mage::helper('catalog')->__('Price'),
                'type' => 'currency',
                'currency_code' => (string) Mage::getStoreConfig(
                    Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
                'index' => 'price'
            ));
        return parent::_prepareColumns();
    }
    
    protected function _getSelectedProducts()
    {
        $products = $this->getProducts();
        if (!is_array($products)) {
            $products = $this->getSelectedProducts();
        }
        return $products;
    }
    
    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->_getData('grid_url') ? $this->_getData('grid_url')
            : $this->getUrl('*/*/productsGrid', array('_current' => true));
    }
    
    public function getSelectedProducts()
    {
        $model = Mage::registry('current_gift_rule');
        
        return $model->getProductIds();
    }
}
