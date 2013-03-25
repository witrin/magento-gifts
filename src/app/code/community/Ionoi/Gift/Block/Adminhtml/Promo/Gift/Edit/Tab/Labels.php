<?php

/**
 * Shopping Cart Gift Rule Labels Tab
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit_Tab_Labels extends
    Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('salesrule')->__('Labels');
    }
    
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('salesrule')->__('Labels');
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
        $rule = Mage::registry('current_gift_rule');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');
        
        $fieldset = $form->addFieldset('default_label_fieldset',
            array(
                'legend' => Mage::helper('salesrule')->__('Default Label')
            ));
        $labels = $rule->getStoreLabels();
        $fieldset->addField('store_default_label',
            'text',
            array(
                'name' => 'rule[store_labels][0]',
                'required' => false,
                'label' => Mage::helper('salesrule')->__('Default Rule Label for All Store Views'),
                'value' => isset($labels[0]) ? $labels[0] : '',
            ));
        
        $fieldset = $form->addFieldset('store_labels_fieldset',
            array(
                'legend' => Mage::helper('salesrule')->__('Store View Specific Labels'),
                'table_class' => 'form-list stores-tree',
            ));
        $renderer = $this->getLayout()
            ->createBlock('adminhtml/store_switcher_form_renderer_fieldset');
        $fieldset->setRenderer($renderer);
        
        foreach (Mage::app()->getWebsites() as $website) {
            $fieldset->addField("w_{$website->getId()}_label",
                'note',
                array(
                    'label' => $website->getName(),
                    'fieldset_html_class' => 'website',
                ));
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                if (count($stores) == 0) {
                    continue;
                }
                $fieldset->addField("sg_{$group->getId()}_label",
                    'note',
                    array(
                        'label' => $group->getName(),
                        'fieldset_html_class' => 'store-group',
                    ));
                foreach ($stores as $store) {
                    $fieldset->addField("s_{$store->getId()}",
                        'text',
                        array(
                            'name' => 'rule[store_labels][' . $store->getId() . ']',
                            'required' => false,
                            'label' => $store->getName(),
                            'value' => isset($labels[$store->getId()])
                            ? $labels[$store->getId()] : '',
                            'fieldset_html_class' => 'store',
                        ));
                }
            }
        }
        
        if ($rule->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }
        
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
