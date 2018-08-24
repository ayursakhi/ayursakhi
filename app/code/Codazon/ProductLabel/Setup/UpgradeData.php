<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductLabel\Setup;

//use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var AggregatedFieldDataConverter
     */
    private $aggregatedFieldConverter;

    /**
     * UpgradeData constructor.
     *
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        //AggregatedFieldDataConverter $aggregatedFieldConverter,
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();
        if (version_compare($version, '2.2.0', '>=') || version_compare($version, '2.2.0-dev', '>=')) {
            $this->convertSerializedDataToJson($setup);
        }

        $setup->endSetup();
    }

    /**
     * Convert metadata from serialized to JSON format:
     *
     * @param ModuleDataSetupInterface $setup
     *
     * @return void
     */
    public function convertSerializedDataToJson($setup)
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->aggregatedFieldConverter = $objectManager->get('Magento\Framework\DB\AggregatedFieldDataConverter');
        $this->aggregatedFieldConverter->convert(
            [
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('codazon_product_label_entity'),
                    'entity_id',
                    'conditions_serialized'
                )
            ],
            $setup->getConnection()
        );
    }
}
