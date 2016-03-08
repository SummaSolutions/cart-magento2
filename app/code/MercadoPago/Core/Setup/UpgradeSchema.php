<?php

namespace MercadoPago\Core\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '1.0.2', '<=')) {
            $invoiceTable = $installer->getTable('sales_invoice');
            $creditMemoTable = $installer->getTable('sales_creditmemo');

            $columns = [
                'finance_cost_amount' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Finance Cost Amount',
                ],
                'base_finance_cost_amount' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Base Finance Cost Amount',
                ]
            ];

            $connection = $installer->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($invoiceTable, $name, $definition);
                $connection->addColumn($creditMemoTable, $name, $definition);
            }
        }

        $setup->endSetup();
    }
}
