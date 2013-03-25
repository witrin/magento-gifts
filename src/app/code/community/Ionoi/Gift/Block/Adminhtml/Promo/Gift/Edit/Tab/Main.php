<?php

/**
 * Shopping Cart Gift Rule General Information Tab
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit_Tab_Main extends
Mage_Adminhtml_Block_Widget_Form implements
Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('salesrule')->__('Rule Information');
    }
    
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('salesrule')->__('Rule Information');
    }
    
    /**
     * Returns status flag about this tab can be showed or not
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
        
        $fieldset = $form->addFieldset('base_fieldset',
            array(
                'legend' => Mage::helper('salesrule')->__('General Information')
            ));
        
        if ($model->getId()) {
            $fieldset->addField('rule_id',
                'hidden',
                array(
                    'name' => 'rule[id]',
                ));
        }
        
        $fieldset->addField('name',
            'text',
            array(
                'name' => 'rule[name]',
                'label' => Mage::helper('salesrule')->__('Rule Name'),
                'title' => Mage::helper('salesrule')->__('Rule Name'),
                'required' => true,
            ));
        
        $fieldset->addField('description',
            'textarea',
            array(
                'name' => 'rule[description]',
                'label' => Mage::helper('salesrule')->__('Description'),
                'title' => Mage::helper('salesrule')->__('Description'),
                'style' => 'height: 100px;',
            ));
        
        $fieldset->addField('is_active',
            'select',
            array(
                'label' => Mage::helper('salesrule')->__('Status'),
                'title' => Mage::helper('salesrule')->__('Status'),
                'name' => 'rule[is_active]',
                'required' => true,
                'options' => array(
                    '1' => Mage::helper('salesrule')->__('Active'),
                    '0' => Mage::helper('salesrule')->__('Inactive'),
                ),
            ));
        
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }
        
        if (Mage::app()->isSingleStoreMode()) {
            $websiteId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids',
                'hidden',
                array(
                    'name' => 'rule[website_ids][]',
                    'value' => $websiteId
                ));
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField('website_ids',
                'multiselect',
                array(
                    'name' => 'rule[website_ids][]',
                    'label' => Mage::helper('salesrule')->__('Websites'),
                    'title' => Mage::helper('salesrule')->__('Websites'),
                    'required' => true,
                    'values' => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
                ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        }
        
        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $found = false;
        
        foreach ($customerGroups as $group) {
            if ($group['value'] == 0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups,
                array(
                    'value' => 0,
                    'label' => Mage::helper('salesrule')->__('NOT LOGGED IN')
                ));
        }
        
        $fieldset->addField('customer_group_ids',
            'multiselect',
            array(
                'name' => 'rule[customer_group_ids][]',
                'label' => Mage::helper('salesrule')->__('Customer Groups'),
                'title' => Mage::helper('salesrule')->__('Customer Groups'),
                'required' => true,
                'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
            ));
        
        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date',
            'date',
            array(
                'name' => 'rule[from_date]',
                'label' => Mage::helper('salesrule')->__('From Date'),
                'title' => Mage::helper('salesrule')->__('From Date'),
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso
            ));
        $fieldset->addField('to_date',
            'date',
            array(
                'name' => 'rule[to_date]',
                'label' => Mage::helper('salesrule')->__('To Date'),
                'title' => Mage::helper('salesrule')->__('To Date'),
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
                'format' => $dateFormatIso
            ));
        
        $fieldset->addField('sort_order',
            'text',
            array(
                'name' => 'rule[sort_order]',
                'label' => Mage::helper('salesrule')->__('Priority'),
            ));
        
        
        
        $form->setValues($model->getData());
        
        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }
        
        //$form->setUseContainer(true);
        
        $this->setForm($form);
        
        Mage::dispatchEvent('adminhtml_gift_rule_edit_tab_main_prepare_form',
            array(
                'form' => $form
            ));
        
        return parent::_prepareForm();
    }
}
