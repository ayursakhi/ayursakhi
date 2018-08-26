<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoExtended\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade Schema script
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        if (!$context->getVersion() || version_compare($context->getVersion(), '2.1.0', '<')) {
            $this->installSeoFilterTable($installer);
        }
    }

    public function installSeoFilterTable($installer)
    {
        $installer->startSetup();

        /**
         * Create table 'mageworx_seoextended_category'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('mageworx_seoextended_category')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'ID'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            5,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Attribute ID'
        )->addColumn(
            'category_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            [
                'unsigned' => true,
                'nullable' => false,
            ],
            'Category ID'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            [
                'unsigned' => true,
                'nullable' => false,
                'primary' => true,
            ],
            'Store ID'
        )->addColumn(
            'meta_title',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65536,
            [
                'nullable' => false,
            ],
            'Meta Title'
        )->addColumn(
            'meta_description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65536,
            [
                'nullable' => false,
            ],
            'Meta Description'
        )->addColumn(
            'description',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            65536,
            [
                'nullable' => false,
            ],
            'Desription'
        )->addColumn(
            'meta_keywords',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            512,
            [
                'nullable' => false,
            ],
            'Meta Keywords'
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_seoextended_category',
                'store_id',
                'store',
                'store_id'
            ),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_seoextended_category',
                'attribute_id',
                'eav_attribute',
                'attribute_id'
            ),
            'attribute_id',
            $installer->getTable('eav_attribute'),
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName(
                'mageworx_seoextended_category',
                'category_id',
                'catalog_category_entity',
                'entity_id'
            ),
            'category_id',
            $installer->getTable('catalog_category_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
