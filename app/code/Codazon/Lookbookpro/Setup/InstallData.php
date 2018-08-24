<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
    
    private $setupFactory;

    public function __construct(
        \Codazon\Lookbookpro\Setup\LookbookproSetupFactory $setupFactory,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory $lookCollectionFactory,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $lookCategoryFactory
    ) {
        $this->setupFactory = $setupFactory;
        $this->_lookCollectionFactory = $lookCollectionFactory;
        $this->_lookCategoryFactory = $lookCategoryFactory;
    }
    
    
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $moduleSetup = $this->setupFactory->create(['setup' => $setup]);
        $moduleSetup->installEntities();
        $setup->endSetup();
    }
    
}
