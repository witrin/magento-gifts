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
     * @see Mage_Wishlist_Model_Item
     */
    const EXCEPTION_CODE_NOT_SALABLE            = 901;
    const EXCEPTION_CODE_HAS_REQUIRED_OPTIONS   = 902;

    /**
     * Rule source
     *
     * @var array
     */
    protected $_rules = array();

    /**
     * Reset rules
     *
     * @var array
     */
    protected $_resetRules = array();

    /**
     * Applied rules
     *
     * @var array
     */
    protected $_appliedRules = array();

    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @return Ionoi_Gift_Model_Rule_Validator
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
     * @return Ionoi_Gift_Model_Mysql4_Rule_Collection
     */
    protected function _getRules()
    {
        $key = $this->getWebsiteId() . '_' . $this->getCustomerGroupId();
        return $this->_rules[$key];
    }

    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param Ionoi_Gift_Model_Rule $rule
     * @param Mage_Sales_Model_Quote_Address $address
     * @return bool
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
    protected function _reset($address)
    {
        $quote = $address->getQuote();

        if (!array_key_exists($quote->getId(), $this->_resetRules)) {
            $this->_resetRules[$quote->getId()] = array();
        }

        if (!array_key_exists($quote->getId(), $this->_appliedRules)) {
            $this->_appliedRules[$quote->getId()] = array();
        }

        foreach ($quote->getAllItems() as $item ) {
            if ($option = $item->getOptionByCode('gift')) {
                $value = unserialize($option->getValue());
                if ($item->getId()) {
                    $this->_resetRules[$quote->getId()][$value['rule_id']] = $value['rule_id'];
                    $quote->removeItem($item->getId());
                } else {
                    $this->_appliedRules[$quote->getId()][$value['rule_id']] = $value['rule_id'];
                }
            }
        }

        $address->setGiftRuleIds('');
        $address->getQuote()->setGiftRuleIds('');
    }

    /**
     * Get product object based on requested product information
     *
     * @see     Mage_Checkout_Model_Cart::_getProduct()
     * @param   mixed $productInfo
     * @return  Mage_Catalog_Model_Product
     */
    protected function _getProduct($productInfo)
    {
        $product = null;
        if ($productInfo instanceof Mage_Catalog_Model_Product) {
            $product = $productInfo;
        } elseif (is_int($productInfo) || is_string($productInfo)) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productInfo);
        }
        $currentWebsiteId = Mage::app()->getStore()->getWebsiteId();
        if (!$product
            || !$product->getId()
            || !is_array($product->getWebsiteIds())
            || !in_array($currentWebsiteId, $product->getWebsiteIds())
        ) {
            Mage::throwException(Mage::helper('checkout')->__('The product could not be found.'));
        }
        return $product;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @see     Mage_Checkout_Model_Cart::_getProductRequest()
     * @param   mixed $requestInfo
     * @return  Varien_Object
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof Varien_Object) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new Varien_Object(array('qty' => $requestInfo));
        } else {
            $request = new Varien_Object($requestInfo);
        }

        if (!$request->hasQty()) {
            $request->setQty(1);
        }

        return $request;
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
        $this->_reset($address);

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = $address->getQuote();
        $messages = array();
        /* @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton('checkout/session');

        foreach ($this->_getRules() as $rule /* @var $rule Ionoi_Gift_Model_Rule */) {
            // Check rule
            if (array_key_exists($rule->getId(), $this->_appliedRules[$quote->getId()]) ||
                 !$this->_canProcessRule($rule, $address)) {
                continue;
            }

            Mage::dispatchEvent('gift_rule_validator_process', compact('rule', 'address'));

            // Create gifts
            foreach ($rule->getProductIds() as $productId) {
                $product = $this->_getProduct($productId);
                $product->addCustomOption('gift', serialize(array('rule_id' => $rule->getId())));

                if ($product->getStatus() != Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    continue;
                }

                if (!$product->isVisibleInSiteVisibility()) {
                    if ($product->getStoreId() == Mage::app()->getStore()->getId()) {
                        continue;
                    }
                }

                if (!$product->isSalable()) {
                    continue;
                }

                // Check if item already exists in cart
                $item = false;
                foreach ($quote->getAllItems() as $_item) {
                    if ($_parent = $_item->getParentItem()) {
                        if ($_parent->getProductId() == $productId) {
                            $this->_resetRules[$quote->getId()][$rule->getId()] = $rule->getId();
                            $item = $_item;
                            $item->setData('gift', array('rule_id' => $rule->getId()));
                            break;
                        }
                    }
                }

                if (!$item) {
                    $request = $this->_getProductRequest($rule->getQty());

                    // Handle configurable products
                    if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                        $products = $product->getTypeInstance(true)->getUsedProducts(null, $product);
                        foreach ($products as $_child) {
                            $options = array();
                            $_attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);
                            foreach ($_attributes as $_attribute) {
                                $productAttribute   = $_attribute->getProductAttribute();
                                $productAttributeId = $productAttribute->getId();
                                $attributeValue     = $_child->getData($productAttribute->getAttributeCode());
                                $options[$productAttributeId] = $attributeValue;
                            }
                            $request->setSuperAttribute($options);
                            break;
                        }
                    }

                    try {
                        $result = $quote->addProduct($product, $request);
                    } catch (Mage_Core_Exception $e) {
                        $session->setUseNotice(false);
                        $result = $e->getMessage();
                    }

                    /**
                     * String we can get if prepare process has error
                     */
                    if (is_string($result)) {
                        Mage::throwException($result);
                        break;
                    }

                    $item = $result;
                }

                /* @var $item Mage_Sales_Model_Quote_Item */
                $item = $item->getParentItem() ? $item->getParentItem() : $item;
                $item->setCustomPrice(0);
                $item->setOriginalCustomPrice(0);
                $item->getProduct()->setIsSuperMode(true);

                // Set messages
                $storeMessage = $rule->getStoreLabel($address->getQuote()->getStore());
                if (!empty($storeMessage)) {
                    $item->setMessage($storeMessage);
                }

                if (!array_key_exists($rule->getId(), $this->_resetRules[$quote->getId()])) {
                    $message = new Mage_Core_Model_Message_Success(
                        Mage::helper('gift')->__(
                            '%s was added as a gift to your shopping cart.',
                            Mage::helper('core')->escapeHtml($product->getName())
                        )
                    );
                    $message->setIdentifier(Mage::helper('gift')->__('gift-rule-%s', $rule->getId()));
                    $messages[] = $message;
                    $session->getMessages()->add($message);
                }
            }

            $this->_appliedRules[$quote->getId()][$rule->getId()] = $rule->getId();

            // $this->_addGiftDescription($address, $rule);

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $address->setGiftRuleIds($this->mergeIds($address->getGiftRuleIds(), $this->_appliedRules[$quote->getId()]));

        $quote->setGiftRuleIds($this->mergeIds($quote->getGiftRuleIds(), $this->_appliedRules[$quote->getId()]));

        if (count($messages) > 0 && !Mage::registry('gift_added_success_messages')) {
            Mage::register('gift_added_success_messages', $messages);
        }

        return $this;
    }

    /**
     * Merge two sets of ids
     *
     * @param array|string $first
     * @param array|string $second
     * @param bool $asString
     * @return array
     */
    public function mergeIds($first, $second, $asString = true)
    {
        if (!is_array($first)) {
            $first = empty($first) ? array() : explode(',', $first);
        }
        if (!is_array($second)) {
            $second = empty($second) ? array() : explode(',', $second);
        }
        $a = array_unique(array_merge($first, $second));
        if ($asString) {
            $a = implode(',', $a);
        }
        return $a;
    }

    /**
     * Add rule gift description label to address object
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param Ionoi_Gift_Model_Rule $rule
     * @return Ionoi_Gift_Model_Rule_Validator
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
