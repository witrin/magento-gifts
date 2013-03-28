<?php

/**
 * Shopping Cart Gift Rule Validator Model
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule_Validator extends Mage_Core_Model_Abstract
{
    /**
     * Rule source collection
     *
     * @var Ionoi_Gift_Model_Resource_Rule_Collection
     */
    protected $_rules;
    
    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param   int $websiteId
     * @param   int $customerGroupId
     * @param   string $couponCode
     * @return  Ionoi_Gift_Model_Rule_Validator
     */
    public function init($websiteId, $customerGroupId)
    {
        $this->setWebsiteId($websiteId)->setCustomerGroupId($customerGroupId);
        
        $key = $websiteId . '_' . $customerGroupId;
        if (!isset($this->_rules[$key])) {
            $this->_rules[$key] = Mage::getResourceModel('gift/rule_collection')
                ->setValidationFilter($websiteId, $customerGroupId)->load();
        }
        return $this;
    }
    
    /**
     * Get rules collection for current object state
     *
     * @return Mage_SalesRule_Model_Mysql4_Rule_Collection
     */
    protected function _getRules()
    {
        $key = $this->getWebsiteId() . '_' . $this->getCustomerGroupId();
        return $this->_rules[$key];
    }
    
    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param   Mage_SalesRule_Model_Rule $rule
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    protected function _canProcessRule($rule, $address)
    {
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }
        
        $rule->afterLoad();
        
        /**
         * quote does not meet rule's conditions
         */
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        
        return true;
    }
    
    /**
     * Reset quote and address applied rules
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Ionoi_Gift_Model_Rule_Validator
     */
    public function reset($address)
    {
        $quote = $address->getQuote();
        $reseted = array();
        foreach ($quote->getAllItems() as $item) {
            if ($option = $item->getOptionByCode('gift')) {
                $value = unserialize($option->getValue());
                $reseted[] = $value['rule_id'];
                $quote->removeItem($item->getId());
            }
        }
        
        $address->setGiftRuleIds('');
        $address->getQuote()->setGiftRuleIds('');
        
        if (!Mage::registry('reseted_gift_rule_ids') && count($reseted) > 0) {
            Mage::register('reseted_gift_rule_ids', $reseted);
        }
        
        return $this;
    }
    
    /**
     * Quote address gift creation process
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Ionoi_Gift_Model_Rule_Validator
     * @todo Labels and stop rules processing flag
     */
    public function process($address)
    {
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $address->getQuote();
        $added = array();
        $reseted = Mage::registry('reseted_gift_rule_ids') ? Mage::registry('reseted_gift_rule_ids') : array();
        $messages = array();
        
        /* @var $rule Ionoi_Gift_Model_Rule */
        foreach ($this->_getRules() as $rule) {
            // check rule
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }
            // dispatch event
            Mage::dispatchEvent('gift_rule_validator_process',
                array(
                    'rule' => $rule,
                    'address' => $address
                ));
            // create gifts
            foreach ($rule->getProductIds() as $productId) {
                // load gift
                $product = Mage::getModel('catalog/product')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                // check availability
                if (!$product->getId() || !is_array($product->getWebsiteIds())
                    || !in_array($this->getWebsiteId(), $product->getWebsiteIds())) {
                    Mage::throwException(Mage::helper('gift')->__('The gift could not be found.'));
                }
                // set option
                $product
                    ->addCustomOption('gift',
                        serialize(array(
                                'rule_id' => $rule->getId()
                            )));
                // add to quote
                if (false == $product->isSalable()) {
                    continue;
                }
                $result = $quote->addProduct($product, $rule->getQty());
                // check result
                if (is_string($result)) {
                    // something went wrong
                    Mage::throwException($result);
                } else {
                    // successfully added
                    /* @var $item Mage_Sales_Model_Quote_Item */
                    $item = $result;
                    // set price
                    $item->setCustomPrice(0);
                    $item->setOriginalCustomPrice(0);
                    $item->getProduct()->setIsSuperMode(true);
                    // set messages
                    $item->setMessage($rule->getStoreLabel($address->getQuote()->getStore()));
                    if (!in_array($rule->getId(), $reseted)) {
                        $messages[] = Mage::helper('gift')
                                ->__('%s was added as a gift to your shopping cart.',
                                    Mage::helper('core')->escapeHtml($product->getName()));
                    }
                }
            }
            
            $added[$rule->getId()] = $rule->getId();
            
            //$this->_addGiftDescription($address, $rule);
            
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        
        $address->setGiftRuleIds($this->mergeIds($address->getGiftRuleIds(), $added));
        
        $quote->setGiftRuleIds($this->mergeIds($quote->getGiftRuleIds(), $added));
        
        if (count($messages) > 0 && !Mage::registry('current_gift_added_success_messages')) {
            Mage::register('current_gift_added_success_messages', $messages);
        }
        
        return $this;
    }
    
    /**
     * Merge two sets of ids
     *
     * @param array|string $a1
     * @param array|string $a2
     * @param bool $asString
     * @return array
     */
    public function mergeIds($first, $second, $asString = true)
    {
        if (!is_array($first)) {
            $first = empty($first) ? array() : explode(',', $first);
        }
        if (!is_array($first)) {
            $first = empty($first) ? array() : explode(',', $first);
        }
        $a = array_unique(array_merge($first, $first));
        if ($asString) {
            $a = implode(',', $a);
        }
        return $a;
    }
    
    /**
     * Add rule gift description label to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Mage_SalesRule_Model_Rule $rule
     * @return  Ionoi_Gift_Model_Rule_Validator
     */
    protected function _addGiftDescription($address, $rule)
    {
        $description = $address->getDiscountDescriptionArray();
        $ruleLabel = $rule->getStoreLabel($address->getQuote()->getStore());
        $label = '';
        if ($ruleLabel) {
            $label = $ruleLabel;
        }
        
        if (strlen($label)) {
            $description[$rule->getId()] = $label;
        }
        
        $address->setGiftDescriptionArray($description);
        
        return $this;
    }
    
    /**
     * Convert address gift description array to string
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param string $separator
     * @return Ionoi_Gift_Model_Rule_Validator
     */
    public function prepareGiftDescription($address, $separator = ', ')
    {
        $description = $address->getGiftDescriptionArray();
        
        if (is_array($description) && !empty($description)) {
            $description = array_unique($description);
            $description = implode($separator, $description);
        } else {
            $description = '';
        }
        $address->setGiftDescription($description);
        return $this;
    }
    
}
