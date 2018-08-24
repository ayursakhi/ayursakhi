<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magefan\Blog\Setup;

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
        \Magefan\Blog\Model\ResourceModel\Post\CollectionFactory $postsFactory
    ) {
        $this->postsFactory = $postsFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $version = $context->getVersion();
        if (version_compare($version, '2.5.3', '<')) {
            $this->cloneImageFieldData();
        }
        $setup->endSetup();
    }

    public function cloneImageFieldData()
    {
        //$themes = $this->designFactory->create();
        $posts = $this->postsFactory->create();
        $posts->addFieldToSelect('*');
        foreach($posts as $post){
            if($post->getPostImage()){
                $post->setFeaturedImg($post->getPostImage());
                $post->save();
            }
        }
    }
}
