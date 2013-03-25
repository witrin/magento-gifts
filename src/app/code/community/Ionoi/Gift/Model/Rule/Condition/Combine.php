<?php

/**
 * Combine Rule Condition Data Model
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule_Condition_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('gift/rule_condition_combine');
    }

    public function getNewChildSelectOptions()
    {
        $addressCondition = Mage::getModel('gift/rule_condition_address');
        $addressAttributes = $addressCondition
            ->loadAttributeOptions()
            ->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code=>$label) {
            $attributes[] = array(
                'value'=>'gift/rule_condition_address|'.$code, 
                'label'=>$label
            );
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value'=>'gift/rule_condition_product_found', 
                'label'=>Mage::helper('salesrule')->__('Product attribute combination')
            ),
            array(
                'value'=>'gift/rule_condition_product_subselect', 
                'label'=>Mage::helper('salesrule')->__('Products subselection')
            ),
            array(
                'value'=>'gift/rule_condition_combine', 
                'label'=>Mage::helper('salesrule')->__('Conditions combination')
            ),
            array(
                'label'=>Mage::helper('salesrule')->__('Cart Attribute'), 
                'value'=>$attributes),
        ));

        $additional = new Varien_Object();
        
        Mage::dispatchEvent('gift_rule_condition_combine', array('additional' => $additional));
        
        if ($additionalConditions = $additional->getConditions()) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
