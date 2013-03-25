<?php

/**
 * Shopping Cart Gift Rules Controller
 * 
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Ionoi_Gift_Adminhtml_Promo_GiftController extends Mage_Adminhtml_Controller_Action
{
    protected function _initRule()
    {
        $this->_title(Mage::helper('salesrule')->__('Promotions'))
            ->_title(Mage::helper('gift')->__('Shopping Cart Gift Rules'));
        
        Mage::register('current_gift_rule', Mage::getModel('gift/rule'));
        $id = (int) $this->getRequest()->getParam('id');
        
        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int) $this->getRequest()->getParam('rule_id');
        }
        
        if ($id) {
            Mage::registry('current_gift_rule')->load($id);
        }
    }
    
    protected function _initAction()
    {
        $this->loadLayout()->_setActiveMenu('promo/gift')
            ->_addBreadcrumb(Mage::helper('salesrule')->__('Promotions'),
                Mage::helper('salesrule')->__('Promotions'));
        return $this;
    }
    
    public function indexAction()
    {
        $this->_title(Mage::helper('salesrule')->__('Promotions'))
            ->_title(Mage::helper('gift')->__('Shopping Cart Gift Rules'));
        
        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('salesrule')->__('Catalog'),
                Mage::helper('salesrule')->__('Catalog'))->renderLayout();
    }
    
    public function newAction()
    {
        $this->_forward('edit');
    }
    
    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('gift/rule');
        
        if ($id) {
            $model->load($id);
            if (!$model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('salesrule')
                            ->__('This rule no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }
        
        $this
            ->_title($model->getRuleId() ? $model->getName()
                    : Mage::helper('salesrule')->__('New Rule'));
        
        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $model->getActions()->setJsFormObject('rule_actions_fieldset');
        
        Mage::register('current_gift_rule', $model);
        
        $this->_initAction()->getLayout()->getBlock('promo_gift_edit')
            ->setData('action', $this->getUrl('*/*/save'));
        
        $this
            ->_addBreadcrumb($id ? Mage::helper('salesrule')->__('Edit Rule')
                    : Mage::helper('salesrule')->__('New Rule'),
                $id ? Mage::helper('salesrule')->__('Edit Rule')
                    : Mage::helper('salesrule')->__('New Rule'))->renderLayout();
        
    }
    
    /**
     * Gift rule save action
     *
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                /** @var $model Mage_Gift_Model_Rule */
                $model = Mage::getModel('gift/rule');
                Mage::dispatchEvent('adminhtml_controller_gift_prepare_save',
                    array(
                        'request' => $this->getRequest()
                    ));
                $data = $this->getRequest()->getPost('rule');
                $data = $this
                    ->_filterDates($data, array(
                        'from_date',
                        'to_date'
                    ));
                
                if (isset($data['id'])) {
                    $model->load($data['id']);
                    if ($data['id'] != $model->getId()) {
                        Mage::throwException(Mage::helper('salesrule')
                                ->__('Wrong rule specified.'));
                    }
                }
                
                $session = Mage::getSingleton('adminhtml/session');
                
                $validateResult = $model
                    ->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                    $session->setPageData($data);
                    $this->_redirect('*/*/edit', array(
                            'id' => $model->getId()
                        ));
                    return;
                }
                
                if (isset($data['products'])) {
                    $data['product_ids'] = Mage::helper('adminhtml/js')
                        ->decodeGridSerializedInput($data['products']);
                    unset($data['products']);
                } else if ($model->getId()) {
                    $data['product_ids'] = $model->getProductIds();
                }
                
                $model->loadPost($data);
                
                $session->setPageData($model->getData());
                
                $model->save();
                $session
                    ->addSuccess(Mage::helper('salesrule')
                            ->__('The rule has been saved.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this
                        ->_redirect('*/*/edit', array(
                            'id' => $model->getId()
                        ));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $id = (int) $this->getRequest()->getParam('rule_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', array(
                            'id' => $id
                        ));
                } else {
                    $this->_redirect('*/*/new');
                }
                return;
                
            } catch (Exception $e) {
                $this->_getSession()
                    ->addError(Mage::helper('salesrule')
                            ->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this
                    ->_redirect('*/*/edit',
                        array(
                            'id' => $this->getRequest()->getParam('rule_id')
                        ));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('gift/rule');
                $model->load($id);
                $model->delete();
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(Mage::helper('salesrule')
                            ->__('The rule has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()
                    ->addError(Mage::helper('salesrule')
                            ->__('An error occurred while deleting the rule. Please review the log and try again.'));
                Mage::logException($e);
                $this
                    ->_redirect('*/*/edit',
                        array(
                            'id' => $this->getRequest()->getParam('id')
                        ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')
            ->addError(Mage::helper('salesrule')
                    ->__('Unable to find a rule to delete.'));
        $this->_redirect('*/*/');
    }
    
    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|',
            str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];
        
        $model = Mage::getModel($type)->setId($id)->setType($type)
            ->setRule(Mage::getModel('gift/rule'))->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }
        
        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
    
    public function applyRulesAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }
    
    public function productsAction()
    {
        $products = $this->getRequest()->getPost('products', null);
        
        $this->_initRule();
        $this->loadLayout()->getLayout()
            ->getBlock('promo_gift_edit_tab_products_grid')
            ->setProducts($products);
        $this->renderLayout();
    }
    
    public function productsGridAction()
    {
        $products = $this->getRequest()->getPost('products', null);
        
        $this->_initRule();
        $this->loadLayout()->getLayout()
            ->getBlock('promo_gift_edit_tab_products_grid')
            ->setProducts($products);
        $this->renderLayout();
    }
    
    /**
     * Chooser source action
     */
    public function chooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $chooserBlock = $this->getLayout()
            ->createBlock('adminhtml/promo_widget_chooser',
                '',
                array(
                    'id' => $uniqId
                ));
        $this->getResponse()->setBody($chooserBlock->toHtml());
    }
    
    /**
     * Returns result of current user permission check on resource and privilege
     * 
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/gift');
    }
}
