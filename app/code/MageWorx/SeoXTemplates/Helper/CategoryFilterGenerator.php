<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoXTemplates\Helper;

class CategoryFilterGenerator extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * CategoryFilterGenerator constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @param \MageWorx\SeoXTemplates\Model\Template\CategoryFilter $template
     * @param null|int $nestedStoreId
     * @return void
     */
    public function createMissingSeoFilters($template, $nestedStoreId = null)
    {
        $storeId = !empty($nestedStoreId) ? $nestedStoreId : $template->getStoreId();

        /** @var \MageWorx\SeoExtended\Model\ResourceModel\CategoryFilter\Collection $categoryFilterCollection */
        $categoryFilterCollection = $template->getCategoryFilterCollectionFactory()->create();
        $categoryFilterCollection->addFieldToFilter('store_id', $storeId);
        $categoryFilterCollection->addFieldToFilter('attribute_id', $template->getAttributeId());
        $issetCategoryIds = $categoryFilterCollection->getColumnValues('category_id');

        /** @var  \MageWorx\SeoExtended\Model\CategoryFilter */
        $categoryFilter = $categoryFilterCollection->getNewEmptyItem();

        $rootId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categoryCollection->addFieldToFilter('path', ['like'=> "1/$rootId/%"]);

        if ($template->isAssignForIndividualItems()) {
            $targetCategoryIds = $template->getCategoriesData();
            $categoryCollection->addIdFilter($targetCategoryIds);
        }

        $categoryIds = $categoryCollection->getAllIds();

        foreach ($categoryIds as $categoryId) {
            if (in_array($categoryId, $issetCategoryIds)) {
                continue;
            }

            $categoryFilter->setId(null);
            $categoryFilter->setAttributeId($template->getAttributeId());
            $categoryFilter->setCategoryId($categoryId);
            $categoryFilter->setStoreId($storeId);
            $categoryFilter->save();
        }
    }
}
