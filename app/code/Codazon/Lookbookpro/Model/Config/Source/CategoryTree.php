<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\Config\Source;

use Magento\Framework\App\ObjectManager;

class CategoryTree implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;
    
    protected $_categoryFactory;
    
    protected $_request;
    
    protected $_rootIds;
    
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }

        return $this->_options;
    }
    
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->_request = ObjectManager::getInstance()->get('Magento\Framework\App\RequestInterface');
        }
        return $this->_request;
    }
    
    public function getCategoryFactory()
    {
        if ($this->_categoryFactory === null) {
            $this->_categoryFactory = ObjectManager::getInstance()
                ->get('Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory');
        }
        return $this->_categoryFactory;
    }
    
    public function getCategoryTree($parentId = null, $storeId = 0) {
        $tree = [];
        $buff = [];
        $collection = $this->getCategoryFactory()->create();
        $collection
            ->setStoreId($storeId)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect(['name'])
            ->getSelect();
        
        if ($parentId !== null) {
            $collection->addFieldToFilter('parent_id', $parentId);
        } else {
            $parentId = 0;
        }
        
        if ($collection->count()) {
            foreach($collection as $category) {
                $id = $category->getId();
                $row = $category->getData();
                $current =& $buff[$id];
                $current = $row;
                if ($row['parent_id'] == $parentId) {
                    $tree[$id] =& $current;
                } else {
                    $buff[$row['parent_id']]['children'][$id] =& $current;
                }
            }
        }
        return $tree;
    }
    
    protected function _getOptions($tree = false, $parentId = null, $storeId = false)
    {
        if ($storeId === false) {
            $storeId = $this->getRequest()->getParam('store', 0);
        }
        if ($tree === false) {
            
            $scopeCode = $storeId? ObjectManager::getInstance()->get('Magento\Store\Model\Store')->load($storeId)->getCode() : 'default';
            $scopeConfig = ObjectManager::getInstance()->get('Magento\Framework\App\Config');
            $rootId = $scopeConfig->getValue('codazon_lookbook/general/root_category', 'store', $scopeCode);
            $tree = $this->getCategoryTree(null, $storeId);
            
            try{
                $tree = [$rootId => $tree[\Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID]['children'][$rootId]];
            } catch (\Exception $e) {
                return [];
            }
        }
        $options = [];
        if (count($tree)) {
            foreach ($tree as $categoryId => $category) {
                $data = ['value' => (string)$categoryId, 'label' => $category['name']];
                if (!empty($category['children'])) {
                    $children = $category['children'];
                    $data['optgroup'] = $this->_getOptions($children, $categoryId, $storeId);
                    if (!count($data['optgroup'])) {
                        unset($data['optgroup']);
                    }
                }
                $options[] = $data;
            }
        }
        return $options;
    }
}

 