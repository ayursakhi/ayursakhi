<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $table = $connection->newTable($installer->getTable('cdzlookbook_entity'))->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            "Entity ID"
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            "Creation Time"
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            "Update Time"
        )->setComment('Lookbook Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_entity_datetime'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_entity_datetime'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_entity_datetime',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_datetime', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_datetime', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_datetime',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_datetime',
                    'entity_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_datetime', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Datetime Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_entity_decimal'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_entity_decimal'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_entity_decimal',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_decimal', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_decimal', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_decimal',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_decimal',
                    'entity_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_decimal', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Decimal Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_entity_int'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_entity_int'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_entity_int',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_int', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_int', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_int', 'entity_id', 'cdzlookbook_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_int', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Integer Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_entity_text'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_entity_text'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_entity_text',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_text', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_text', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_text',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_text',
                    'entity_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Text Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_entity_varchar'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_entity_varchar'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_entity_varchar',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_varchar', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_entity_varchar', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_varchar',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_entity_varchar',
                    'entity_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_entity_varchar', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Varchar Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        $table = $connection->newTable($installer->getTable('cdzlookbook_item_entity'))->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            "Entity ID"
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            "Creation Time"
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            "Update Time"
        )->setComment('Lookbook Item Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_item_entity_datetime'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_entity_datetime'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_entity_datetime',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_datetime', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_datetime', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_datetime',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_datetime',
                    'entity_id',
                    'cdzlookbook_item_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_datetime', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Item Datetime Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_item_entity_decimal'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_entity_decimal'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_entity_decimal',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_decimal', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_decimal', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_decimal',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_decimal',
                    'entity_id',
                    'cdzlookbook_item_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_decimal', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Item Decimal Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_item_entity_int'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_entity_int'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_entity_int',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_int', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_int', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_int', 'entity_id', 'cdzlookbook_item_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_int', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Item Integer Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_item_entity_text'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_entity_text'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_entity_text',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_text', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_text', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_text',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_text',
                    'entity_id',
                    'cdzlookbook_item_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Item Text Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_item_entity_varchar'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_entity_varchar'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_entity_varchar',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_varchar', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_item_entity_varchar', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_varchar',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_entity_varchar',
                    'entity_id',
                    'cdzlookbook_item_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_item_entity_varchar', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Item Varchar Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        $table = $connection->newTable($installer->getTable('cdzlookbook_category_entity'))->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
            "Entity ID"
        )->addColumn(
            'created_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            "Creation Time"
        )->addColumn(
            'updated_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            "Update Time"
        )->addColumn(
            'parent_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            "Parent ID"
        )->addColumn(
            'path',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            "Tree Path"
        )->addColumn(
            'position',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            "Position"
        )->addColumn(
            'level',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true],
            "Tree Level"
        )->addColumn(
            'children_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            "Child Count"
        )->setComment('Lookbook Category Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_category_entity_datetime'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_entity_datetime'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_entity_datetime',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_datetime', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_datetime', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_datetime',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_datetime',
                    'entity_id',
                    'cdzlookbook_category_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_datetime', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Category Datetime Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_category_entity_decimal'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_entity_decimal'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_entity_decimal',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_decimal', ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_decimal', ['attribute_id']),
                ['attribute_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_decimal',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_decimal',
                    'entity_id',
                    'cdzlookbook_category_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_decimal', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Category Decimal Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_category_entity_int'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_entity_int'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_entity_int',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_int', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_int', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_int', 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_int', 'entity_id', 'cdzlookbook_category_entity', 'entity_id'),
                'entity_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_int', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Category Integer Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_category_entity_text'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_entity_text'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                '64k',
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_entity_text',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_text', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_text', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_text',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_text',
                    'entity_id',
                    'cdzlookbook_category_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Category Text Attribute Backend Table');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'cdzlookbook_category_entity_varchar'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_entity_varchar'))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Value'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_entity_varchar',
                    ['entity_id', 'attribute_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_varchar', ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $installer->getIdxName('cdzlookbook_category_entity_varchar', ['store_id']),
                ['store_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_varchar',
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $installer->getTable('eav_attribute'),
                'attribute_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_entity_varchar',
                    'entity_id',
                    'cdzlookbook_category_entity',
                    'entity_id'
                ),
                'entity_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('cdzlookbook_category_entity_varchar', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook Category Varchar Attribute Backend Table');
        $installer->getConnection()->createTable($table);
        
        
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_category_lookbook'))
            ->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Category ID'
            )
            ->addColumn(
                'lookbook_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Lookbook ID'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_category_lookbook',
                    ['category_id', 'lookbook_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['category_id', 'lookbook_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_lookbook',
                    'category_id',
                    'cdzlookbook_category_entity',
                    'entity_id'
                ),
                'category_id',
                $installer->getTable('cdzlookbook_category_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_category_lookbook',
                    'lookbook_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'lookbook_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Category - Lookbook Table');
        $installer->getConnection()->createTable($table);
        
        
        $table = $installer->getConnection()
            ->newTable($installer->getTable('cdzlookbook_item_group'))
            ->addColumn(
                'lookbook_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Lookbook ID'
            )
            ->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Item ID'
            )
            ->addColumn(
                'position',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Position'
            )
            ->addIndex(
                $installer->getIdxName(
                    'cdzlookbook_item_group',
                    ['lookbook_id', 'item_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['lookbook_id', 'item_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_group',
                    'lookbook_id',
                    'cdzlookbook_entity',
                    'entity_id'
                ),
                'lookbook_id',
                $installer->getTable('cdzlookbook_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'cdzlookbook_item_group',
                    'item_id',
                    'cdzlookbook_item_entity',
                    'entity_id'
                ),
                'item_id',
                $installer->getTable('cdzlookbook_item_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Lookbook - Item Table');
        $installer->getConnection()->createTable($table);
        

        $installer->endSetup();
    }
}

