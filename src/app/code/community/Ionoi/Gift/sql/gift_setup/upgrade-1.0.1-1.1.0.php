<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

/**
 * Add coupon code to table 'gift/rule'
 */
$installer->getConnection()
    ->addColumn(
        $installer->getTable('gift/rule'),
        'coupon_code',
        array(
            'type'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length'   => 255,
            'comment'  => 'Coupon Code',
        )
    );
