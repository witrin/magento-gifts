<?php

/**
 * Shopping Cart Gift Rule Tabs
 *
 * @category Gift
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Block_Adminhtml_Promo_Gift_Edit_Tabs extends
Mage_Adminhtml_Block_Widget_Tabs
{
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('promo_catalog_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('gift')->__('Shopping Cart Gift Rule'));
    }
    
    protected function _prepareLayout()
    {
        $this->addTab('main_section',
            array(
                'label' => Mage::helper('salesrule')->__('General Information'),
                'title' => Mage::helper('salesrule')->__('General Information'),
                'content' => $this->_translateHtml($this->getLayout()
                        ->createBlock('gift/adminhtml_promo_gift_edit_tab_main')
                        ->toHtml())
            ));
        $this->addTab('conditions',
            array(
                'label' => Mage::helper('salesrule')->__('Conditions'),
                'title' => Mage::helper('salesrule')->__('Conditions'),
                'content' => $this->_translateHtml($this->getLayout()
                        ->createBlock('gift/adminhtml_promo_gift_edit_tab_conditions')
                        ->toHtml())
            ));
        $this->addTab('labels',
            array(
                'label' => Mage::helper('salesrule')->__('Labels'),
                'title' => Mage::helper('salesrule')->__('Labels'),
                'content' => $this->_translateHtml($this->getLayout()
                        ->createBlock('gift/adminhtml_promo_gift_edit_tab_labels')
                        ->toHtml())
            ));
        $this->addTab('products',
            array(
                'label' => Mage::helper('gift')->__('Gifts'),
                'title' => Mage::helper('gift')->__('Gifts'),
                'url' => $this->getUrl('*/*/products',
                    array(
                        '_current' => true
                    )),
                'class' => 'ajax',
            ));
    }
    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        Mage::getSingleton('core/translate_inline')->processResponseBody($html);
        return $html;
    }
}
