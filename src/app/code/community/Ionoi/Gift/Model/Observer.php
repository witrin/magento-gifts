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
     * Called after message is added to abstract session
     * 
     * @param unknown_type $observer
     * @return Ionoi_Gift_Model_Observer
     */
    public function onAbstractSessionMessageAdded($observer) 
    {
        $session = Mage::getSingleton('checkout/session');
        $this->_reorderSuccessMessages($session);
        
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
        
        $validator->process($address);
    }
    
    /**
     * Reorder success messages
     * 
     * @param Mage_Checkout_Model_Session $session
     */
    public function _reorderSuccessMessages($session)
    {
        $messages = Mage::registry('gift_added_success_messages');
        
        if (!$messages || !($session instanceof Mage_Checkout_Model_Session)) {
            return;
        }
        
        foreach ($messages as $message) {
            /* @var $message Mage_Core_Model_Message_Abstract */
            $session->getMessages()->deleteMessageByIdentifier($message->getIdentifier());
            $session->getMessages()->add($message);
        }
    }
}
