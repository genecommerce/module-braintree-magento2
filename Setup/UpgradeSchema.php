<?php

namespace Magento\Braintree\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Magento\Braintree\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // 3.0.0
        if (version_compare($context->getVersion(), '3.0.0', '<')) {
            $this->braintreeTransactionDetails($installer);
            $this->braintreeCreditPrices($installer);
        }

        // 3.1.0
        if (version_compare($context->getVersion(), '3.1.0', '<')) {
            $this->braintreeAddWebsiteFieldToCreditPrices($installer);
        }

        $installer->endSetup();
    }

    /**
     * Create the braintree_transaction_details table
     * @param SchemaSetupInterface $installer
     */
    private function braintreeTransactionDetails(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'braintree_transaction_details'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('braintree_transaction_details'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Id'
            )
            ->addColumn(
                'transaction_source',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                12,
                ['nullable' => true],
                'Transaction Source'
            )
            ->addIndex(
                $installer->getIdxName('braintree_transaction_details', ['order_id']),
                ['order_id']
            )
            ->addForeignKey(
                $installer->getFkName('braintree_transaction_details', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Braintree transaction details table');
        $installer->getConnection()->createTable($table);
    }

    /**
     * Create the braintree_credit_prices table
     * @param SchemaSetupInterface $installer
     */
    private function braintreeCreditPrices(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('braintree_credit_prices'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Row ID'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product Id'
            )
            ->addColumn(
                'term',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                4,
                ['nullable' => false],
                'Credit Term'
            )
            ->addColumn(
                'monthly_payment',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false],
                'Monthly Payment'
            )
            ->addColumn(
                'instalment_rate',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false],
                'Instalment Rate'
            )
            ->addColumn(
                'cost_of_purchase',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false],
                'Cost of purchase'
            )
            ->addColumn(
                'total_inc_interest',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,2',
                ['nullable' => false],
                'Total Inc Interest'
            )
            ->addIndex(
                $installer->getIdxName('braintree_credit_prices', ['product_id']),
                ['product_id']
            )
            ->addIndex(
                $installer->getIdxName(
                    'braintree_credit_prices',
                    ['product_id', 'term'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['product_id', 'term'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setComment('Braintree credit rates');
        $installer->getConnection()->createTable($table);
    }

    /**
     * Add website_id field to the braintree_credit_prices table
     * @param SchemaSetupInterface $installer
     */
    private function braintreeAddWebsiteFieldToCreditPrices(SchemaSetupInterface $installer)
    {
        $installer->getConnection()->addColumn(
            $installer->getTable('braintree_credit_prices'),
            'website_id',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'size' => null,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'Website Id'
            ]
        );
    }
}
