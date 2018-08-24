<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\ThemeOptions\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param SalesSetupFactory $salesSetupFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Theme\Model\ResourceModel\Design\CollectionFactory $designFactory,
        \Magento\Config\Model\ResourceModel\ConfigFactory $configFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Codazon\ThemeOptions\Model\ResourceModel\Config\Data\CollectionFactory $cdzConfigDataFactory,
        \Codazon\ThemeOptions\Model\ResourceModel\Config\DataFactory $cdzDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->designFactory = $designFactory;
        $this->configFactory = $configFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->cdzConfigDataFactory = $cdzConfigDataFactory;
        $this->cdzDataFactory = $cdzDataFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            $this->removeDesignChange();
            $this->cloneDefaultToStoreConfig();
        }
        $setup->endSetup();
    }

    public function removeDesignChange()
    {
        $config = $this->configFactory->create();
        $coll = $this->designFactory->create();
        foreach($coll as $design){
            if($design->getStoreId()){
                $config->saveConfig(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID, $design->getDesign(), \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $design->getStoreId());
                $design->delete();
            }
        }
        $this->indexerRegistry->get(\Magento\Theme\Model\Data\Design\Config::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
    }

    protected function _checkExistData($themeId, $storeId){
        $coll = $this->cdzConfigDataFactory->create();
        $coll->addScopeFilter(\Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId, $themeId, '');
        if($coll->getSize() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function cloneDefaultToStoreConfig()
    {
        //$themes = $this->designFactory->create();
        $data = $this->cdzDataFactory->create();
        $stores = $this->storeManager->getStores();
        foreach($stores as $store){
            $themeId = $store->getConfig(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID);
            $storeId = $store->getId();
            if(!$this->_checkExistData($themeId, $storeId)){
                $coll = $this->cdzConfigDataFactory->create();
                $coll->addScopeFilter('default', '0', $themeId, '');
                foreach($coll as $cfg){
                    //if default exist data thene clone it to child stores if child store empty data
                    $data->saveConfig($themeId, $cfg->getPath(), $cfg->getValue(), 'stores', $storeId);
                }
            }
        }
    }
}
