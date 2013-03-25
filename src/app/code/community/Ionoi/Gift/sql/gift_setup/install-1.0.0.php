<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Create table 'gift/rule'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/rule'))
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Rule Id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Name')
    ->addColumn('description',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        '64k',
        array(),
        'Description')
    ->addColumn('from_date',
        Varien_Db_Ddl_Table::TYPE_DATE,
        null,
        array(
            'nullable' => true,
            'default' => null
        ),
        'From Date')
    ->addColumn('to_date',
        Varien_Db_Ddl_Table::TYPE_DATE,
        null,
        array(
            'nullable' => true,
            'default' => null
        ),
        'To Date')
    ->addColumn('is_active',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable' => false,
            'default' => '0',
        ),
        'Is Active')
    ->addColumn('stop_rules_processing',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'nullable' => false,
            'default' => '1',
        ),
        'Stop Rules Processing')
    ->addColumn('conditions_serialized',
        Varien_Db_Ddl_Table::TYPE_TEXT,
        '2M',
        array(),
        'Conditions Serialized')
    ->addColumn('sort_order',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ),
        'Sort Order')
    ->addColumn('qty',
        Varien_Db_Ddl_Table::TYPE_DECIMAL,
        array(
            12,
            4
        ),
        array(
            'nullable' => false,
            'default' => '0',
        ),
        'Qty')
    ->addColumn('times_used',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ),
        'Times Used')
    ->addIndex($installer
            ->getIdxName('gift/rule',
                array(
                    'is_active',
                    'sort_order',
                    'to_date',
                    'from_date'
                )),
        array(
            'is_active',
            'sort_order',
            'to_date',
            'from_date'
        ))->setComment('Gift Rule');
$installer->getConnection()->createTable($table);

/**
 * Create table 'gift/website' if not exists.
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/website'))
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Rule Id')
    ->addColumn('website_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Website Id')
    ->addIndex($installer
            ->getIdxName('gift/website', array(
                'rule_id'
            )),
        array(
            'rule_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/website', array(
                'website_id'
            )),
        array(
            'website_id'
        ))
    ->addForeignKey($installer
            ->getFkName('gift/website', 'rule_id', 'gift/rule', 'rule_id'),
        'rule_id',
        $installer->getTable('gift/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer
            ->getFkName('gift/website',
                'website_id',
                'core/website',
                'website_id'),
        'website_id',
        $installer->getTable('core/website'),
        'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Gift Rules To Websites Relations');

$installer->getConnection()->createTable($table);

/**
 * Create table 'gift/customer_group' if not exists.
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/customer_group'))
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Rule Id')
    ->addColumn('customer_group_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Customer Group Id')
    ->addIndex($installer
            ->getIdxName('gift/customer_group', array(
                'rule_id'
            )),
        array(
            'rule_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/customer_group',
                array(
                    'customer_group_id'
                )),
        array(
            'customer_group_id'
        ))
    ->addForeignKey($installer
            ->getFkName('gift/customer_group',
                'rule_id',
                'gift/rule',
                'rule_id'),
        'rule_id',
        $installer->getTable('gift/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer
            ->getFkName('gift/customer_group',
                'customer_group_id',
                'customer/customer_group',
                'customer_group_id'),
        'customer_group_id',
        $installer->getTable('customer/customer_group'),
        'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Gift Rules To Customer Groups Relations');
$installer->getConnection()->createTable($table);

/**
 * Create table 'gift/label'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/label'))
    ->addColumn('label_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Label Id')
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Rule Id')
    ->addColumn('store_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
        ),
        'Store Id')
    ->addColumn('label', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Label')
    ->addIndex($installer
            ->getIdxName('gift/label',
                array(
                    'rule_id',
                    'store_id'
                ),
                Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array(
            'rule_id',
            'store_id'
        ),
        array(
            'type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ))
    ->addIndex($installer
            ->getIdxName('gift/label', array(
                'store_id'
            )),
        array(
            'store_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/label', array(
                'rule_id'
            )),
        array(
            'rule_id'
        ))
    ->addForeignKey($installer
            ->getFkName('gift/label', 'rule_id', 'gift/rule', 'rule_id'),
        'rule_id',
        $installer->getTable('gift/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer
            ->getFkName('gift/label', 'store_id', 'core/store', 'store_id'),
        'store_id',
        $installer->getTable('core/store'),
        'store_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)->setComment('Gift Label');
$installer->getConnection()->createTable($table);

/**
 * Create table 'gift/product' if not exists.
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/product'))
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Rule Id')
    ->addColumn('product_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true
        ),
        'Product Id')
    ->addIndex($installer
            ->getIdxName('gift/product', array(
                'rule_id'
            )),
        array(
            'rule_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/product', array(
                'product_id'
            )),
        array(
            'product_id'
        ))
    ->addForeignKey($installer
            ->getFkName('gift/product', 'rule_id', 'gift/rule', 'rule_id'),
        'rule_id',
        $installer->getTable('gift/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer
            ->getFkName('gift/product',
                'product_id',
                'catalog/product',
                'product_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Gift Rules To Product Relations');
$installer->getConnection()->createTable($table);

/**
 * Create table 'gift/product_attribute'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('gift/product_attribute'))
    ->addColumn('rule_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Rule Id')
    ->addColumn('website_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Website Id')
    ->addColumn('customer_group_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Customer Group Id')
    ->addColumn('attribute_id',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        array(
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ),
        'Attribute Id')
    ->addIndex($installer
            ->getIdxName('gift/product_attribute',
                array(
                    'website_id'
                )),
        array(
            'website_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/product_attribute',
                array(
                    'customer_group_id'
                )),
        array(
            'customer_group_id'
        ))
    ->addIndex($installer
            ->getIdxName('gift/product_attribute',
                array(
                    'attribute_id'
                )),
        array(
            'attribute_id'
        ))
    ->addForeignKey($installer
            ->getFkName('gift/product_attribute',
                'attribute_id',
                'eav/attribute',
                'attribute_id'),
        'attribute_id',
        $installer->getTable('eav/attribute'),
        'attribute_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey($installer
            ->getFkName('gift/product_attribute',
                'customer_group_id',
                'customer/customer_group',
                'customer_group_id'),
        'customer_group_id',
        $installer->getTable('customer/customer_group'),
        'customer_group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey($installer
            ->getFkName('gift/product_attribute',
                'rule_id',
                'gift/rule',
                'rule_id'),
        'rule_id',
        $installer->getTable('gift/rule'),
        'rule_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->addForeignKey($installer
            ->getFkName('gift/product_attribute',
                'website_id',
                'core/website',
                'website_id'),
        'website_id',
        $installer->getTable('core/website'),
        'website_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_NO_ACTION)
    ->setComment('Gift Product Attribute');
$installer->getConnection()->createTable($table);

$installer->endSetup();


$installer = new Mage_Sales_Model_Resource_Setup('core_setup');

$installer->startSetup();

/**
 * Add 'gift_rule_ids' attribute for entities
 */
$entities = array(
    'quote',
    'quote_address',
    'order'
);
$options = array(
    'type' => Varien_Db_Ddl_Table::TYPE_VARCHAR,
    'visible' => false,
    'required' => false
);
foreach ($entities as $entity) {
    $installer->addAttribute($entity, 'gift_rule_ids', $options);
}

$installer->endSetup();
