<?php

/**
 * Shopping Cart Gift Rules
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'gift';
        $this->_controller = 'adminhtml_promo_gift';
        $this->_headerText = Mage::helper('gift')->__('Shopping Cart Gift Rules');
        $this->_addButtonLabel = Mage::helper('salesrule')->__('Add New Rule');
        parent::__construct();
    }
    
    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }
        return $this->getUrl('*/promo_save/save');
    }
}
