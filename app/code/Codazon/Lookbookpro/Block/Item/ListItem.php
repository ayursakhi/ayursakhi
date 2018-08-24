<?php
/**
 * Copyright © 2018 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Item;

use Codazon\Lookbookpro\Model\LookbookItem;
use Codazon\Lookbookpro\Model\ResourceModel\LookbookItem\Collection;
use Codazon\Lookbookpro\Block\Lookbook\ItemListToolbar as Toolbar;
use Codazon\Lookbookpro\Model\Toolbar as ToolbarModel;

class ListItem extends \Codazon\Lookbookpro\Block\Item\AbstractItem
{   
    protected $_lookbookCollection;
    
    protected $_defaultToolbarBlock = Toolbar::class;
    
    protected $_currentCategory;
    
    const LIMIT_VAR_NAME = ToolbarModel::LIMIT_PARAM_NAME;
    const PAGE_VAR_NAME = 'p';
    const DEFAULT_ITEMS_PER_PAGE = 16;
    const AVAILABLE_LIMIT = [
        16 => 16,
        32 => 32,
        64 => 64
    ];
    
    public function getItemLoadedCollection()
    {
        if (!$this->_lookbookCollection) {
             $lookbook = $this->_coreRegistry->registry('current_lookbook');
             $this->_lookbookCollection = $lookbook->getItemCollection()
                ->setPageSize($this->getItemsPerPage())
                ->setCurPage($this->getRequest()->getParam(self::PAGE_VAR_NAME, 1));
                
        }
        return $this->_lookbookCollection;
    }
    
    public function getCurrentLookbook()
    {
        if ($this->_currentCategory === null) {
            $this->_currentCategory = $this->_coreRegistry->registry('current_lookbook');
        }
        return $this->_currentCategory;
    }
    
    public function getItemsPerPage()
    {
        return $this->getRequest()->getParam(self::LIMIT_VAR_NAME, self::DEFAULT_ITEMS_PER_PAGE);
    }
    
    protected function _beforeToHtml()
    {
        $collection = $this->getItemLoadedCollection();
        $collection->load();
        $this->configureToolbar($this->getToolbarBlock(), $collection);
        

        return parent::_beforeToHtml();
    }
    
    public function getToolbarHtml()
    {
        return $this->getChildHtml('item_list_toolbar');
    }
    
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }
    
    private function configureToolbar(Toolbar $toolbar, Collection $collection)
    {
        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }
        
        $toolbar->setCollection($collection);
        $this->setChild('item_list_toolbar', $toolbar);
    }
    
    public function getCacheKeyInfo()
    {
        return [
            $this->_cacheTag,
            $this->getRequest()->getParam('id', 0),
            $this->_storeManager->getStore()->getId(),
            $this->getRequest()->getParam(self::PAGE_VAR_NAME, 1),
            $this->getRequest()->getParam(self::LIMIT_VAR_NAME, self::DEFAULT_ITEMS_PER_PAGE),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP)
        ];
    }

	public function getIdentities()
    {
        $identities = [];
        $lookbook = $this->getCurrentLookbook();
        $identities[] = [LookbookItem::CACHE_LOOKBOOK_ITEM_TAG . '_' . ($lookbook? : $lookbook->getId())];
        return $identities; 
    }
    
}