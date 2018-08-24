<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\Store;

class Tree extends \Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory\AbstractCategory
{
    protected $_template = 'lookbook_category/tree.phtml';
    
    protected $_backendSession;
    
    protected $_resourceHelper;
    
    protected $_jsonEncoder;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_resourceHelper = $resourceHelper;
        $this->_backendSession = $backendSession;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }
    
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(0);
    }
    
    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/new", ['_current' => false, 'id' => null, '_query' => false]);
        if ($this->getStore()->getId() == Store::DEFAULT_STORE_ID) {
            $this->addChild(
                'add_sub_button', \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Add Subcategory'),
                    'onclick' => "addNew('" . $addUrl . "', false)",
                    'class' => 'add',
                    'id' => 'add_subcategory_button',
                    'style' => $this->canAddSubCategory() ? '' : 'display: none;'
                ]
            );

            if ($this->canAddRootCategory()) {
                $this->addChild(
                    'add_root_button', \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Add Root Category'),
                        'onclick' => "addNew('" . $addUrl . "', true)",
                        'class' => 'add',
                        'id' => 'add_root_category_button'
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }
    
    public function getSuggestedCategoriesJson($namePart)
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());

        /* @var $collection Collection */
        $collection = $this->_categoryFactory->create()->getCollection();

        $matchingNamesCollection = clone $collection;
        $escapedNamePart = $this->_resourceHelper->addLikeEscape(
            $namePart,
            ['position' => 'any']
        );
        $matchingNamesCollection->addAttributeToFilter(
            'name',
            ['like' => $escapedNamePart]
        )->addAttributeToFilter(
            'entity_id',
            ['neq' => \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID]
        )->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );

        $shownCategoriesIds = [];
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        $collection->addAttributeToFilter(
            'entity_id',
            ['in' => array_keys($shownCategoriesIds)]
        )->addAttributeToSelect(
            ['name', 'is_active', 'parent_id']
        )->setStoreId(
            $storeId
        );

        $categoryById = [
            \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID => [
                'id' => \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['children'][] = & $categoryById[$category->getId()];
        }

        return $this->_jsonEncoder->encode($categoryById[\Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID]['children']);
    }
    
    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }
    
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }
    
    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }
    
    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }
    
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }
    
    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current' => true, 'entity_id' => null, 'store' => null];
        if (is_null($expanded) && $this->_backendSession->getIsTreeWasExpanded() || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }
    
    public function getNodesUrl()
    {
        return $this->getUrl('catalog/category/jsonTree');
    }
    
    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            'lookbookpro/lookbookcategory/tree',
            ['_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }
    
    public function getIsWasExpanded()
    {
        return $this->_backendSession->getIsTreeWasExpanded();
    }
    
    public function getMoveUrl()
    {
        return $this->getUrl('lookbookpro/lookbookcategory/move', ['store' => $this->getRequest()->getParam('store')]);
    }
    
    public function getTree($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : [];
        return $tree;
    }
    
    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $json = $this->_jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }
    
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $categories = $this->_categoryTree->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($path);
        if (empty($categories)) {
            return '';
        }
        foreach ($categories as $key => $category) {
            $categories[$key] = $this->_getNodeJson($category);
        }
        return '<script>require(["prototype"], function(){' . $javascriptVarName . ' = ' . $this->_jsonEncoder->encode(
            $categories
        ) .
            ';' .
            ($this->canAddSubCategory() ? '$("add_subcategory_button").show();' : '$("add_subcategory_button").hide();') .
            '});</script>';
    }
    
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);

        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id'] = $node->getId();
        $item['store'] = (int)$this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && ($node->getLevel() == 1 && $rootForStores ? false : true);

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }

        $isParent = $this->_isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }
    
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        if ($this->_withLookbookCount) {
            $result .= ' (' . $node->getLoobookCount() . ')';
        }
        return $result;
    }
    
    protected function _isCategoryMoveable($node)
    {
        $options = new \Magento\Framework\DataObject(['is_moveable' => true, 'category' => $node]);

        $this->_eventManager->dispatch('adminhtml_lookbook_category_tree_is_moveable', ['options' => $options]);

        return $options->getIsMoveable();
    }
    
    protected function _isParentSelectedCategory($node)
    {
        if ($node && $this->getCategory()) {
            $pathIds = $this->getCategory()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }
    
    public function isClearEdit()
    {
        return (bool)$this->getRequest()->getParam('clear');
    }
    
    public function canAddRootCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_lookbook_category_tree_can_add_root_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }
    
    public function canAddSubCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_lookbook_category_tree_can_add_sub_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }
    
}