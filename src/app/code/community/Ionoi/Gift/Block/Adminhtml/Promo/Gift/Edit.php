<?php

/**
 * Shopping Cart Gift Rule Edit Form
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    
    /**
     * Initialize form
     * 
     */
    public function __construct()
    {
        $this->_blockGroup = 'gift';
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_promo_gift';
        
        parent::__construct();
        
        $this->_addButton('save_and_continue_edit', array(
            'class'   => 'save',
            'label'   => Mage::helper('salesrule')->__('Save and Continue Edit'),
            'onclick' => 'editForm.submit($(\'edit_form\').action + \'back/edit/\')',
        ), 10);
    }
    
    /**
     * Getter for form header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $rule = Mage::registry('current_gift_rule');
        if ($rule->getRuleId()) {
            return Mage::helper('salesrule')->__("Edit Rule '%s'", $this->escapeHtml($rule->getName()));
        } else {
            return Mage::helper('salesrule')->__('New Rule');
        }
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
        return $this->getUrl('*/promo_gift/save');
    }
    
    /**
     * Retrieve products JSON
     *
     * @return string
     */
    public function getProductsJson()
    {
        return '{}';
    }
}
