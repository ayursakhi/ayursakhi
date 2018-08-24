<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Category;

class CategoryList extends \Magento\Framework\View\Element\Template
{
    protected $_helper;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_assetRepository;
    protected $_collectionFactory;
    protected $_coreRegistry;
    protected $_category;
    protected $_activeId;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Codazon\Lookbookpro\Helper\Data $helper,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_collectionFactory = $collectionFactory;
		$this->_helper = $helper;
		$this->_scopeConfig = $context->getScopeConfig();
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    
    public function getHelper()
    {
        return $this->_helper;
    }
    
    public function getCategoryTree($parentId = null) {
        $tree = [];
        $buff = [];
        $collection = $this->_collectionFactory->create();
        $storeId = $this->_storeManager->getStore()->getId();
        $collection
            ->setStoreId($storeId)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect(['name', 'url_key', 'url_path']);
        
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
    
    protected function _getItemAttributes($category, $level, $activeId = 0)
    {
        $class = ['item'];
        $class[] = 'level-' . $level;
        if (!empty($category['children'])) {
            $class[] = 'parent';
        }
        if ($activeId == $category['entity_id']) {
            $class[] = 'current-item';
        }
        $attributes['class'] = implode(' ', $class);
        $attributes['data-id'] = $category['entity_id'];
        $attributes['data-level'] = $level;
        $string = [];
        foreach ($attributes as $attribute => $value) {
            $string[] = $attribute . '="' . $value . '"';
        }
        return implode(' ', $string);
    }
    
    protected function _getMenuHtml($categories, $activeId, $level = 0)
    {
        $html = '';
        usort($categories, function($a, $b) {
            try {
                return $a['position'] - $b['position'];
            } catch (\Exceptions $e) {
                return $a['entity_id'] - $b['entity_id'];
            }
        });
        foreach ($categories as $category) {
            $html .= '<li '.$this->_getItemAttributes($category, $level, $activeId).'>';
            $html .= '<a class="menu-link" title="'. $category['name'] .'" href="'. $this->_helper->getCategoryUrlByKey($category['url_path'], $category['entity_id']) .'">'. $category['name'] .'</a>';
            if (!empty($category['children'])) {
                $html .= '<ul class="sub-category">';
                $html .= $this->_getMenuHtml($category['children'], $activeId, $level + 1);
                $html .= '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
    
    public function getTreeRootId()
    {
        return \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
    }
    
    public function getHtml() {
        $parentId = $this->getData('parent_id');
        $categories = $this->getCategoryTree($parentId);
        if (!$parentId) {
            $treeRootId = $this->_helper->getLookbookRootCategoryId();
            $storeRootId = $this->_helper->getStoreRootCategoryId();
            try {
                if (isset($categories[$treeRootId]['children'][$storeRootId]['children'])) {
                    $categories = $categories[$treeRootId]['children'][$storeRootId]['children'];
                } else {
                    $categories = [];
                }
            } catch(\Exceptions $e) {
                $categories = [];
            }
        }
        if ($categories) {
            return $this->_getMenuHtml($categories, $this->getActiveId(), 0);
        } else {
            return '';
        }
    }
    
    public function getCurrentCategory()
    {
        if ($this->_category === null) {
            $this->_category = $this->_coreRegistry->registry('lookbook_category');
        }
        return $this->_category;
    }
    
    public function getActiveId()
    {
        if ($this->_activeId == null) {
            $currentCategory = $this->getCurrentCategory();
            if ($currentCategory) {
                if ($currentCategory->getId()) {
                    $this->_activeId = $currentCategory->getId();
                } else {
                    $this->_activeId = $this->getTreeRootId();
                }
            } else {
                $this->_activeId = $this->getTreeRootId();
            }
        }
        return $this->_activeId;
    }
    
    public function getMenuConfig()
    {
        $config = [];
        $menuType = $this->_helper->getConfig('codazon_lookbook/lookbook_category_list/display_type');
        switch ($menuType) {
            case '1' :
                $parentId = null;
                break;
            case '2' :
            default:
                $parentId = $this->getActiveId();
                break;
        }
        $config['parent_id'] = $parentId;
        return $config;
    }
    
    
}