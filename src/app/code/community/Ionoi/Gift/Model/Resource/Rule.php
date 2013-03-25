<?php

/**
 * Shopping Cart Gift Rule Resource Model
 *
 * @category Gift
 * @package Ionoi_Gift
 * @author Artus Kolanowski <artus@ionoi.net>
 */
class Ionoi_Gift_Model_Resource_Rule extends Mage_Rule_Model_Resource_Abstract
{
    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = array(
        'website' => array(
            'associations_table' => 'gift/website',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'website_id'
        ),
        'customer_group' => array(
            'associations_table' => 'gift/customer_group',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id'
        ),
        'product' => array(
            'associations_table' => 'gift/product',
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'product_id'
        )
    );
    
    /**
     * Initialize main table and table id field
     */
    protected function _construct()
    {
        $this->_init('gift/rule', 'rule_id');
    }
    
    /**
     * Add customer group ids and website ids to rule data after load
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        $object->setData('customer_group_ids',
            (array) $this->getCustomerGroupIds($object->getId()));
        $object->setData('website_ids',
            (array) $this->getWebsiteIds($object->getId()));
        $object->setData('product_ids',
            (array) $this->getProductIds($object->getId()));
        
        parent::_afterLoad($object);
        return $this;
    }
    
    /**
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);
        return $this;
    }
    
    /**
     * Bind gift rule to customer group(s) and website(s).
     * Save rule's associated store labels.
     * Save product attributes used in rule.
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }
        
        if ($object->hasWebsiteIds()) {
            $websiteIds = $object->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = explode(',', (string) $websiteIds);
            }
            $this->bindRuleToEntity($object->getId(), $websiteIds, 'website');
        }
        
        if ($object->hasCustomerGroupIds()) {
            $customerGroupIds = $object->getCustomerGroupIds();
            if (!is_array($customerGroupIds)) {
                $customerGroupIds = explode(',', (string) $customerGroupIds);
            }
            $this->bindRuleToEntity($object->getId(),
                $customerGroupIds,
                'customer_group');
        }
        
        if ($object->hasProductIds()) {
            $productIds = $object->getProductIds();
            if (!is_array($productIds)) {
                $productIds = explode(',', (string) $productIds);
            }
            
            $this->bindRuleToEntity($object->getId(), $productIds, 'product');
            
        }
        
        // Save product attributes used in rule
        $ruleProductAttributes = array_merge($this->getProductAttributes(serialize($object->getConditions()
                ->asArray())),
        $this->getProductAttributes(serialize($object->getActions()
                ->asArray())));
        if (count($ruleProductAttributes)) {
            $this->setActualProductAttributes($object, $ruleProductAttributes);
        }
        
        return parent::_afterSave($object);
    }
    
    /**
     * Save rule labels for different store views
     *
     * @param int $ruleId
     * @param array $labels
     *
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    public function saveStoreLabels($ruleId, $labels)
    {
        $deleteByStoreIds = array();
        $table = $this->getTable('gift/label');
        $adapter = $this->_getWriteAdapter();
        
        $data = array();
        foreach ($labels as $storeId => $label) {
            if (Mage::helper('core/string')->strlen($label)) {
                $data[] = array(
                    'rule_id' => $ruleId,
                    'store_id' => $storeId,
                    'label' => $label
                );
            } else {
                $deleteByStoreIds[] = $storeId;
            }
        }
        
        $adapter->beginTransaction();
        try {
            if (!empty($data)) {
                $adapter->insertOnDuplicate($table,
                    $data,
                    array(
                        'label'
                    ));
            }
            
            if (!empty($deleteByStoreIds)) {
                $adapter->delete($table,
                    array(
                        'rule_id=?' => $ruleId,
                        'store_id IN (?)' => $deleteByStoreIds
                    ));
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
            
        }
        $adapter->commit();
        
        return $this;
    }
    
    /**
     * Get all existing rule labels
     *
     * @param int $ruleId
     * @return array
     */
    public function getStoreLabels($ruleId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getTable('gift/label'),
            array(
                'store_id',
                'label'
            ))
            ->where('rule_id = :rule_id');
        return $this->_getReadAdapter()
            ->fetchPairs($select, array(
                ':rule_id' => $ruleId
            ));
    }
    
    /**
     * Get rule label by specific store id
     *
     * @param int $ruleId
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel($ruleId, $storeId)
    {
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->getTable('gift/label'), 'label')
            ->where('rule_id = :rule_id')
            ->where('store_id IN(0, :store_id)')
            ->order('store_id DESC');
        return $this->_getReadAdapter()
            ->fetchOne($select,
            array(
                ':rule_id' => $ruleId,
                ':store_id' => $storeId
            ));
    }
    
    /**
     * Retrieve customer group ids of specified rule
     *
     * @param int $ruleId
     * @return array
     */
    public function getProductIds($ruleId)
    {
        return $this->getAssociatedEntityIds($ruleId, 'product');
    }
    
    /**
     * Return codes of all product attributes currently used in gift rules for specified customer group and website
     *
     * @param unknown_type $websiteId
     * @param unknown_type $customerGroupId
     * @return mixed
     */
    public function getActiveAttributes($websiteId, $customerGroupId)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array(
                'a' => $this->getTable('gift/product_attribute')
            ),
            new Zend_Db_Expr('DISTINCT ea.attribute_code'))
            ->joinInner(array(
                'ea' => $this->getTable('eav/attribute')
            ),
            'ea.attribute_id = a.attribute_id',
            array());
        return $read->fetchAll($select);
    }
    
    /**
     * Save product attributes currently used in conditions and actions of rule
     *
     * @param Mage_SalesRule_Model_Rule $rule
     * @param mixed $attributes
     * @return Mage_SalesRule_Model_Resource_Rule
     */
    public function setActualProductAttributes($rule, $attributes)
    {
        $write = $this->_getWriteAdapter();
        $write->delete($this->getTable('gift/product_attribute'),
            array(
                'rule_id=?' => $rule->getId()
            ));
        
        //Getting attribute IDs for attribute codes
        $attributeIds = array();
        $select = $this->_getReadAdapter()
            ->select()
            ->from(array(
                'a' => $this->getTable('eav/attribute')
            ),
            array(
                'a.attribute_id'
            ))
            ->where('a.attribute_code IN (?)', array(
                $attributes
            ));
        $attributesFound = $this->_getReadAdapter()
            ->fetchAll($select);
        if ($attributesFound) {
            foreach ($attributesFound as $attribute) {
                $attributeIds[] = $attribute['attribute_id'];
            }
            
            $data = array();
            foreach ($rule->getCustomerGroupIds() as $customerGroupId) {
                foreach ($rule->getWebsiteIds() as $websiteId) {
                    foreach ($attributeIds as $attribute) {
                        $data[] = array(
                            'rule_id' => $rule->getId(),
                            'website_id' => $websiteId,
                            'customer_group_id' => $customerGroupId,
                            'attribute_id' => $attribute
                        );
                    }
                }
            }
            $write->insertMultiple($this->getTable('gift/product_attribute'),
                $data);
        }
        
        return $this;
    }
    
    /**
     * Collect all product attributes used in serialized rule's action or condition
     *
     * @param string $serializedString
     *
     * @return array
     */
    public function getProductAttributes($serializedString)
    {
        $result = array();
        if (preg_match_all('~s:32:"gift/rule_condition_product";s:9:"attribute";s:\d+:"(.*?)"~s',
        $serializedString,
        $matches)) {
            foreach ($matches[1] as $offset => $attributeCode) {
                $result[] = $attributeCode;
            }
        }
        
        return $result;
    }
    
    /**
     * Bind specified rules to entities
     *
     * @param array|int|string $ruleIds
     * @param array|int|string $entityIds
     * @param string $entityType
     *
     * @return Mage_Rule_Model_Resource_Abstract
     */
    public function bindRuleToEntity($ruleIds, $entityIds, $entityType)
    {
        if (empty($ruleIds)) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $entityInfo = $this->_getAssociatedEntityInfo($entityType);
        
        if (!is_array($ruleIds)) {
            $ruleIds = array(
                (int) $ruleIds
            );
        }
        if (!is_array($entityIds)) {
            $entityIds = array(
                (int) $entityIds
            );
        }
        
        $data = array();
        $count = 0;
        
        $adapter->beginTransaction();
        
        try {
            foreach ($ruleIds as $ruleId) {
                foreach ($entityIds as $entityId) {
                    $data[] = array(
                        $entityInfo['entity_id_field'] => $entityId,
                        $entityInfo['rule_id_field'] => $ruleId
                    );
                    $count++;
                    if (($count % 1000) == 0) {
                        $adapter->insertOnDuplicate($this->getTable($entityInfo['associations_table']),
                            $data,
                            array(
                                $entityInfo['rule_id_field']
                            ));
                        $data = array();
                    }
                }
            }
            if (!empty($data)) {
                $adapter->insertOnDuplicate($this->getTable($entityInfo['associations_table']),
                    $data,
                    array(
                        $entityInfo['rule_id_field']
                    ));
            }
            
            $adapter->delete($this->getTable($entityInfo['associations_table']),
                $adapter->quoteInto($entityInfo['rule_id_field'] . ' IN (?)',
                    $ruleIds)
                . (!empty($entityIds)
                ? ' AND '
                . $adapter->quoteInto($entityInfo['entity_id_field']
                    . ' NOT IN (?)',
                    $entityIds) : ''));
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
            
        }
        
        $adapter->commit();
        
        return $this;
    }
}
