<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Block;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\NodeFactory;

class SearchInCategory extends \Magento\Framework\View\Element\Template
{
        /**
     * Cache identities
     *
     * @var array
     */
    protected $identities = [];

    /**
     * Top menu data tree
     *
     * @var \Magento\Framework\Data\Tree\Node
     */
    protected $_menu;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry;
	 /**
	 * Catalog category
	 *
	 * @var \Magento\Catalog\Helper\Category
	 */
	protected $catalogCategory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $categoryFlatState;

    /**
     * @var MenuCategoryData
     */
    protected $menuCategoryData;
    /**
     * @param Template\Context $context
     * @param NodeFactory $nodeFactory
     * @param TreeFactory $treeFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        NodeFactory $nodeFactory,
        TreeFactory $treeFactory,
		\Codazon\ThemeOptions\Helper\Category $catalogCategory,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Catalog\Observer\MenuCategoryData $menuCategoryData,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['CDZ_CATEGORY_SEARCH',
        ], ]);
        $this->catalogCategory = $catalogCategory;
        $this->categoryFlatState = $categoryFlatState;
        $this->menuCategoryData = $menuCategoryData;
		
        $this->nodeFactory = $nodeFactory;
        $this->treeFactory = $treeFactory;
    }

    /**
     * Get top menu html
     *
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string
     */
    public function getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
    {
        $this->_menu = $this->nodeFactory->create(
            [
                'data' => [],
                'idField' => 'root',
                'tree' => $this->treeFactory->create()
            ]
        );
		$parentId = $this->getData('parent_id')?$this->getData('parent_id'):$this->_storeManager->getStore()->getRootCategoryId();
		$this->_addCategoriesToMenu($this->catalogCategory->getCategoriesByParentId($parentId), $this->_menu, $this);
		$this->_menu->setOutermostClass($outermostClass);
        $this->_menu->setChildrenWrapClass($childrenWrapClass);
		if($this->getData('level')){
			$this->_menu->setLevel($this->getData('level'));
		}

        $html = $this->_getHtml($this->_menu, $childrenWrapClass, $limit);
		
        $transportObject = new \Magento\Framework\DataObject(['html' => $html]);

        $html = $transportObject->getHtml();

        return $html;
    }
	
	protected function _addCategoriesToMenu($categories, $parentCategoryNode, $block)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }
            $block->addIdentity(\Magento\Catalog\Model\Category::CACHE_TAG . '_' . $category->getId());

            $tree = $parentCategoryNode->getTree();
            $categoryData = $this->menuCategoryData->getMenuCategoryData($category);
            $categoryNode = new \Magento\Framework\Data\Tree\Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if ($this->categoryFlatState->isFlatEnabled() && $category->getUseFlatResource()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode, $block);
        }
    }
	
    /**
     * Count All Subnavigation Items
     *
     * @param \Magento\Backend\Model\Menu $items
     * @return int
     */
    protected function _countItems($items)
    {
        $total = $items->count();
        foreach ($items as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->hasChildren()) {
                $total += $this->_countItems($item->getChildren());
            }
        }
        return $total;
    }

    /**
     * Building Array with Column Brake Stops
     *
     * @param \Magento\Backend\Model\Menu $items
     * @param int $limit
     * @return array|void
     *
     * @todo: Add Depth Level limit, and better logic for columns
     */
    protected function _columnBrake($items, $limit)
    {
        $total = $this->_countItems($items);
        if ($total <= $limit) {
            return;
        }

        $result[] = ['total' => $total, 'max' => (int)ceil($total / ceil($total / $limit))];

        $count = 0;
        $firstCol = true;

        foreach ($items as $item) {
            $place = $this->_countItems($item->getChildren()) + 1;
            $count += $place;

            if ($place >= $limit) {
                $colbrake = !$firstCol;
                $count = 0;
            } elseif ($count >= $limit) {
                $colbrake = !$firstCol;
                $count = $place;
            } else {
                $colbrake = false;
            }

            $result[] = ['place' => $place, 'colbrake' => $colbrake];

            $firstCol = false;
        }

        return $result;
    }

    /**
     * Add sub menu HTML code for current menu item
     *
     * @param \Magento\Framework\Data\Tree\Node $child
     * @param string $childLevel
     * @param string $childrenWrapClass
     * @param int $limit
     * @return string HTML code
     */
    protected function _addSubMenu($child, $childLevel, $childrenWrapClass, $limit)
    {
        $html = '';
        if (!$child->hasChildren()) {
            return $html;
        }

        $colStops = null;
        if ($childLevel == 0 && $limit) {
            $colStops = $this->_columnBrake($child->getChildren(), $limit);
        }

        $html .= '<ul>';
        $html .= $this->_getHtml($child, $childrenWrapClass, $limit, $colStops);
        $html .= '</ul>';

        return $html;
    }

    /**
     * Recursively generates top menu html from data that is specified in $menuTree
     *
     * @param \Magento\Framework\Data\Tree\Node $menuTree
     * @param string $childrenWrapClass
     * @param int $limit
     * @param array $colBrakes
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHtml(
        \Magento\Framework\Data\Tree\Node $menuTree,
        $childrenWrapClass,
        $limit,
        $colBrakes = []
    ) {
        $html = '';
		
		$itemCount = $this->getData('item_count');
		
        $children = $menuTree->getChildren();
        $parentLevel = $menuTree->getLevel();
        $childLevel = ($parentLevel === null) ? 1 : $parentLevel + 1;

        $counter = 1;
        $itemPosition = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        foreach ($children as $child) {
            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel == 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $child->setClass($outermostClass);
            }

            $html .= '<li' . ($child->hasChildren()?' class="parent"':'') . '>';
            $html .= '<a data-id="'. str_replace('category-node-', '', $child->getId()) .'" ' . $outermostClassCode . '>' . $this->escapeHtml(
                $child->getName()
            ) . '</a>' . $this->_addSubMenu(
                $child,
                $childLevel,
                $childrenWrapClass,
                $limit
            ) . '</li>';
            $itemPosition++;
			if($itemCount){
				if($itemCount == $counter){
					break;		
				}
			}
            $counter++;
        }

        if (count($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    /**
     * Generates string with all attributes that should be present in menu item element
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return string
     */
    protected function _getRenderedMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $html = '';
        $attributes = $this->_getMenuItemAttributes($item);
        foreach ($attributes as $attributeName => $attributeValue) {
            $html .= ' ' . $attributeName . '="' . str_replace('"', '\"', $attributeValue) . '"';
        }
        return $html;
    }

    /**
     * Returns array of menu item's attributes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemAttributes(\Magento\Framework\Data\Tree\Node $item)
    {
        $menuItemClasses = $this->_getMenuItemClasses($item);
        return ['class' => implode(' ', $menuItemClasses)];
    }

    /**
     * Returns array of menu item's classes
     *
     * @param \Magento\Framework\Data\Tree\Node $item
     * @return array
     */
    protected function _getMenuItemClasses(\Magento\Framework\Data\Tree\Node $item)
    {
        $classes = [];

		//$classes[] = 'item';

        if ($item->getIsLast()) {
            $classes[] = 'last';
        }

        if ($item->getClass()) {
            $classes[] = $item->getClass();
        }

        if ($item->hasChildren()) {
            $classes[] = 'parent';
        }

        return $classes;
    }

    /**
     * Add identity
     *
     * @param array $identity
     * @return void
     */
    public function addIdentity($identity)
    {
        $this->identities[] = $identity;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return $this->identities;
    }
    
    protected function _toHtml()
    {
        parent::_toHtml();
        return $this->getHtml();
    }
    
    public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        return [
            'CDZ_CATEGORY_SEARCH',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId()
        ];
    }
    
}