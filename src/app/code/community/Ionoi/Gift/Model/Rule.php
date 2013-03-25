<?php


/**
 * Shopping Cart Gift Rule Data Model
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule extends Mage_Rule_Model_Abstract
{
    
    /**
     * Prefix of model events names
     *
     * @var string
     */
    
    protected $_eventPrefix = 'gift_rule';
    
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getRule() in this case
     *
     * @var string
     */
    protected $_eventObject = 'rule';
    
    /**
     * Store already validated addresses and validation results
     *
     * @var array
     */
    protected $_validatedAddresses = array();
    
    /**
     * Set resource model and Id field name
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('gift/rule');
        $this->setIdFieldName('rule_id');
    }
    
    /**
     * Set coupon code and uses per coupon
     *
     * @return Mage_SalesRule_Model_Rule
     */
    protected function _afterLoad()
    {
        return parent::_afterLoad();
    }
    
    /**
     * Save/delete coupon
     *
     * @return Mage_SalesRule_Model_Rule
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }
    
    /**
     * Initialize rule model data from array.
     * Set store labels if applicable.
     *
     * @param array $data
     *
     * @return Mage_SalesRule_Model_Rule
     */
    public function loadPost(array $data)
    {
        parent::loadPost($data);
        
        if (isset($data['store_labels'])) {
            $this->setStoreLabels($data['store_labels']);
        }
        
        return $this;
    }
    
    /**
     * Get rule condition combine model instance
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Combine
     */
    public function getConditionsInstance()
    {
        return Mage::getModel('gift/rule_condition_combine');
    }
    
    /**
     * Get rule condition product combine model instance
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Combine
     */
    public function getActionsInstance()
    {
        return Mage::getModel('gift/rule_condition_product_combine');
    }
    
    /**
     * Get gift rule customer group Ids
     *
     * @return array
     */
    public function getCustomerGroupIds()
    {
        if (!$this->hasCustomerGroupIds()) {
            $customerGroupIds = $this->_getResource()
                ->getCustomerGroupIds($this->getId());
            $this->setData('customer_group_ids', (array) $customerGroupIds);
        }
        return $this->_getData('customer_group_ids');
    }
    
    /**
     * Get gift rule products group Ids
     *
     * @return array
     */
    public function getProductIds()
    {
        if (!$this->hasProductIds()) {
            $productIds = $this->_getResource()
                ->getProductIds($this->getId());
            $this->setData('product_ids', (array) $productIds);
        }
        return $this->_getData('product_ids');
    }
    
    /**
     * Get Rule label by specified store
     *
     * @param Mage_Core_Model_Store|int|bool|null $store
     *
     * @return string|bool
     */
    public function getStoreLabel($store = null)
    {
        $storeId = Mage::app()->getStore($store)
            ->getId();
        $labels = (array) $this->getStoreLabels();
        
        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }
        
        return false;
    }
    
    /**
     * Set if not yet and retrieve rule store labels
     *
     * @return array
     */
    public function getStoreLabels()
    {
        if (!$this->hasStoreLabels()) {
            $labels = $this->_getResource()
                ->getStoreLabels($this->getId());
            $this->setStoreLabels($labels);
        }
        
        return $this->_getData('store_labels');
    }
    
    /**
     * Check cached validation result for specific address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    public function hasIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId]) ? true : false;
    }
    
    /**
     * Set validation result for specific address to results cache
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   bool $validationResult
     * @return  Mage_SalesRule_Model_Rule
     */
    public function setIsValidForAddress($address, $validationResult)
    {
        $addressId = $this->_getAddressId($address);
        $this->_validatedAddresses[$addressId] = $validationResult;
        return $this;
    }
    
    /**
     * Get cached validation result for specific address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    public function getIsValidForAddress($address)
    {
        $addressId = $this->_getAddressId($address);
        return isset($this->_validatedAddresses[$addressId])
            ? $this->_validatedAddresses[$addressId] : false;
    }
    
    /**
     * Return id for address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  string
     */
    private function _getAddressId($address)
    {
        if ($address instanceof Mage_Sales_Model_Quote_Address) {
            return $address->getId();
        }
        return $address;
    }
}
