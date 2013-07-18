<?php

/**
 * Product Subselect Rule Condition Data Model
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Model_Rule_Condition_Product_Subselect extends Ionoi_Gift_Model_Rule_Condition_Product_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('gift/rule_condition_product_subselect')
            ->setValue(null);
    }

    public function loadArray($arr, $key='conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    public function asXml($containerKey='conditions', $itemKey='condition')
    {
        $xml = '<attribute>'.$this->getAttribute().'</attribute>'
            . '<operator>'.$this->getOperator().'</operator>'
            . parent::asXml($containerKey, $itemKey);
        return $xml;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array(
            'qty'  => Mage::helper('salesrule')->__('total quantity'),
            'base_row_total'  => Mage::helper('salesrule')->__('total amount'),
        ));
        return $this;
    }

    public function loadValueOptions()
    {
        return $this;
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=='  => Mage::helper('rule')->__('is'),
            '!='  => Mage::helper('rule')->__('is not'),
            '>='  => Mage::helper('rule')->__('equals or greater than'),
            '<='  => Mage::helper('rule')->__('equals or less than'),
            '>'   => Mage::helper('rule')->__('greater than'),
            '<'   => Mage::helper('rule')->__('less than'),
            '()'  => Mage::helper('rule')->__('is one of'),
            '!()' => Mage::helper('rule')->__('is not one of'),
        ));
        return $this;
    }

    public function getValueElementType()
    {
        return 'text';
    }

    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml().
        
        Mage::helper('salesrule')->__(
            "If %s %s %s for a subselection of items in cart matching %s of these conditions:", 
            $this->getAttributeElement()->getHtml(), 
            $this->getOperatorElement()->getHtml(), 
            $this->getValueElement()->getHtml(), 
            $this->getAggregatorElement()->getHtml()
        );
        
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
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
        if (!$this->getConditions()) {
            return false;
        }
        
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($object->getQuote()->getAllItems() as $item) {
            if (parent::validate($item)) {
                $total += $item->getData($attr);
            }
        }
        
        return $this->validateAttribute($total);
    }
}
