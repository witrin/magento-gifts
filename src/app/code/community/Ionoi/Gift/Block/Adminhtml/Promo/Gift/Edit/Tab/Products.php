<?php

/**
 * Shopping Cart Gift Rule Products Tab
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit_Tab_Products extends
    Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('gift')->__('Gifts');
    }
    
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('gift')->__('Gifts');
    }
    
    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }
    
    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }
    
    protected function _prepareForm()
    {
        $model = Mage::registry('current_gift_rule');
        
        $form = new Varien_Data_Form();
        
        $form->setHtmlIdPrefix('rule_');
        
        $fieldset = $form->addFieldset('products_fieldset',
            array(
                'legend' => Mage::helper('gift')->__('Update shopping cart using the following settings')
            ));
        
        $fieldset->addField('stop_rules_processing',
            'select',
            array(
                'label' => Mage::helper('salesrule')->__('Stop Further Rules Processing'),
                'title' => Mage::helper('salesrule')->__('Stop Further Rules Processing'),
                'name' => 'rule[stop_rules_processing]',
                'options' => array(
                    '1' => Mage::helper('salesrule')->__('Yes'),
                    '0' => Mage::helper('salesrule')->__('No'),
                ),
            ));
        
        $fieldset->addField('qty',
            'text',
            array(
                'name' => 'rule[qty]',
                'label' => Mage::helper('catalog')->__('Qty'),
            ));
        
        $form->setValues($model->getData());
        
        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }
        
        $this->setForm($form);
        
        Mage::dispatchEvent('adminhtml_gift_rule_edit_tab_products_prepare_form',
            array(
                'form' => $form
            ));
        
        return parent::_prepareForm();
    }
}
