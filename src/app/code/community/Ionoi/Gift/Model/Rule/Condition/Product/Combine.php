<?php

/**
 * Product Combine Rule Condition Data Model
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule_Condition_Product_Combine extends Mage_Rule_Model_Condition_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('gift/rule_condition_product_combine');
    }

    public function getNewChildSelectOptions()
    {
        $productCondition = Mage::getModel('gift/rule_condition_product');
        $productAttributes = $productCondition
            ->loadAttributeOptions()
            ->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        
        foreach ($productAttributes as $code=>$label) {
            if (strpos($code, 'quote_item_')===0) {
                $iAttributes[] = array(
                    'value'=>'gift/rule_condition_product|'.$code, 
                    'label'=>$label
                );
            } else {
                $pAttributes[] = array(
                    'value'=>'gift/rule_condition_product|'.$code, 
                    'label'=>$label
                );
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive($conditions, array(
            array(
                'value'=>'gift/rule_condition_product_combine', 
                'label'=>Mage::helper('catalog')->__('Conditions Combination')
            ),
            array(
                'label'=>Mage::helper('catalog')->__('Cart Item Attribute'), 
                'value'=>$iAttributes
            ),
            array(
                'label'=>Mage::helper('catalog')->__('Product Attribute'), 
                'value'=>$pAttributes
            ),
        ));
        return $conditions;
    }

    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
