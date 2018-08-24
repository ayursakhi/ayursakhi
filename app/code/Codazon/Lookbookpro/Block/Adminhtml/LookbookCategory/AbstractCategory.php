<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\Store;

class AbstractCategory extends \Magento\Backend\Block\Template
{
    
    protected $_coreRegistry = null;
    
    protected $_categoryTree;
    
    protected $_categoryFactory;
    
    protected $_withLookbookCount;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_categoryTree = $categoryTree;
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->_withLookbookCount = false;
        parent::__construct($context, $data);
    }
    
    public function getCategory()
    {
        return $this->_coreRegistry->registry('lookbookpro_cdzlookbook_category');
    }
    
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
    }
    
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }
    
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
    }
    
    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }
    
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }
    
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
            }

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }
    
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }
    
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->_categoryFactory->create()->getCollection();

            $collection->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_active'
            )->setStoreId(
                $storeId
            );

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }
    
    public function getRootByIds($ids)
    {
        $root = $this->_coreRegistry->registry('root');
        if (null === $root) {
            $ids = $this->_categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree = $this->_categoryTree->loadByIds($ids);
            $rootId = \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }
    
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->_categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }
    
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false, 'store' => $this->getStore()->getId()];
        $params = array_merge($params, $args);
        return $this->getUrl('lookbookpro/*/save', $params);
    }
    
    public function getEditUrl()
    {
        return $this->getUrl(
            'lookbookpro/lookbookcategory/edit',
            ['store' => null, '_query' => false, 'entity_id' => null, 'parent' => null]
        );
    }
    
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [\Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID];
            foreach ($this->_storeManager->getStores() as $store) {
                $ids[] = $store->getConfig('codazon_lookbook/general/root_category');
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
    
}