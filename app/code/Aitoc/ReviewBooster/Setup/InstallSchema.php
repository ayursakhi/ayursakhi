<?php
namespace Aitoc\ReviewBooster\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('aitoc_review_booster_review_detail_extended'))
            ->addColumn(
                'extended_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                20,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Review extended id'
            )
            ->addColumn(
                'review_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                20,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Review id'
            )
            ->addColumn(
                'aitoc_product_advantages',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Product advantages'
            )
            ->addColumn(
                'aitoc_product_disadvantages',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unsigned' => false, 'nullable' => true, 'default' => null],
                'Product disadvantages'
            )
            ->addColumn(
                'aitoc_review_helpful',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Review helpful'
            )
            ->addColumn(
                'aitoc_review_unhelpful',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                1,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Review unhelpful'
            )
            ->addColumn(
                'aitoc_review_reported',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Is review reported'
            )
            ->addColumn(
                'aitoc_customer_verified',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Is customer verified'
            )
            ->addIndex(
                $installer->getIdxName('aitoc_review_booster_review_detail_extended', ['review_id']),
                ['review_id']
            )
            ->setComment(
                'Review extended detail information'
            );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('aitoc_review_booster_reminder'))
            ->addColumn(
                'reminder_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                20,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Reminder id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Store id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Customer Id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0],
                'Order id'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['unsigned' => false, 'nullable' => false, 'default' => 'pending'],
                'Status'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created at'
            )->addColumn(
                'sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Sent at'
            )
            ->addColumn(
                'customer_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer name'
            )
            ->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer email'
            )
            ->addColumn(
                'products',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                ['nullable' => false],
                'Products'
            )
            ->addIndex(
                $installer->getIdxName('aitoc_review_booster_reminder', ['order_id']),
                ['order_id']
            )
            ->setComment(
                'Review reminders'
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
