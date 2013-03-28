<?php

/**
 * Shopping Cart Gift Rule Observer
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Observer
{
    
    public function __construct()
    {
    }
    
    /**
     * Called after product added to cart
     * 
     * @param unknown_type $observer
     * @return Ionoi_Gift_Model_Observer
     */
    public function onCartProductAdded($observer)
    {
        $event = $observer->getEvent();
        $product = $event->getProduct();
        Mage::register('current_cart_product_added', $product);
        return $this;
    }
    
    /**
     * Called after message is added to abstract session
     * 
     * @param unknown_type $observer
     * @return Ionoi_Gift_Model_Observer
     */
    public function onAbstractSessionMessageAdded($observer) 
    {
        $session = Mage::getSingleton('checkout/session');
        $this->_addSuccessMessages($session);
        
        return $this;
    }
    
    /**
     * Called after quote address totals are collected
     * 
     * @param unknown_type $observer
     * @return Ionoi_Gift_Model_Observer
     */
    public function onQuoteAddressTotalsCollected($observer)
    {
        $address = $observer->getQuoteAddress();
        $this->_processGiftRules($address);
        
        return $this;
    }
    
    /**
     * Process the gift rules
     * 
     * @param Mage_Sales_Model_Quote_Address $address
     */
    protected function _processGiftRules($address)
    {
        $quote = $address->getQuote();
        $store = Mage::app()->getStore($quote->getStoreId());
        $validator = Mage::getSingleton('gift/rule_validator')
            ->init($store->getWebsiteId(), $quote->getCustomerGroupId());
        
        $validator->reset($address)->process($address);
    }
    
    /**
     * Add success messages to the end of the checkout session message collection
     * 
     * @param Mage_Checkout_Model_Session $session
     */
    public function _addSuccessMessages($session)
    {
        $messages = Mage::registry('current_gift_added_success_messages');
        $product = Mage::registry('current_cart_product_added');
        $adding = Mage::registry('adding_gift_added_success_messages');
        
        if ($adding || !$messages || !$product || !($session instanceof Mage_Checkout_Model_Session)) {
            return;
        }
        
        $needle = Mage::helper('checkout')->__('%s was added to your shopping cart.', 
            Mage::helper('core')->escapeHtml($product->getName())
        );
        
        foreach ($session->getMessages()->getItems(Mage_Core_Model_Message::SUCCESS) as $message) {
            if ($message->getText() == $needle) {
                Mage::register('adding_gift_added_success_messages', true);
                foreach ($messages as $message) {
                    $session->addSuccess($message);
                }
                Mage::unregister('adding_gift_added_success_messages');
                Mage::unregister('current_gift_added_success_messages');
                Mage::unregister('current_cart_product_added');
                break;
            }
        }
    }
}
