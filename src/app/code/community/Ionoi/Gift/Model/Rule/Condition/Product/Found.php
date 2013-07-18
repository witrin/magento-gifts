<?php

/**
 * Product Found Rule Condition Data Model
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule_Condition_Product_Found extends Ionoi_Gift_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('gift/rule_condition_product_found');
    }

    /**
     * Load value options
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Found
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(
            1 => Mage::helper('salesrule')->__('FOUND'),
            0 => Mage::helper('salesrule')->__('NOT FOUND')
        ));
        return $this;
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() 
            . Mage::helper('salesrule')->__("If an item is %s in the cart with %s of these conditions true:", 
                $this->getValueElement()->getHtml(), 
                $this->getAggregatorElement()->getHtml()
            );
        
        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        
        return $html;
    }

    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;
        
        foreach ($object->getAllItems() as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }
        
        if ($found && $true) {
            // found an item and we're looking for existing one
            return true;
        } else if (!$found && !$true) {
            // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
}
