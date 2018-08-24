<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Category;

class View extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_coreRegistry;

    protected $_helper;
        
    protected $_category;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Codazon\Lookbookpro\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_storeManager = $context->getStoreManager();
		$this->_helper = $helper;
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $category = $this->getCurrentCategory();
        if ($category) {
            if (!$category->getData('layout_prepared')) {
                $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
                
                $title = $category->getName();
                $metaTitle = $category->getMetaTitle()?:$title;
                $this->pageConfig->getTitle()->set($metaTitle);
                
                $description = $category->getMetaDescription()?:$category->getDescription();
                if ($description) {
                    $this->pageConfig->setDescription($description);
                }
                $keywords = $category->getMetaKeywords();

                if ($keywords) {
                    $this->pageConfig->setKeywords($keywords);
                }
                            
                $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
                if ($pageMainTitle) {
                    $pageMainTitle->setPageTitle($title);
                }
                
                /* facebook meta tag */
                $this->pageConfig->setMetadata('og:url', $category->getUrl());
                $this->pageConfig->setMetadata('og:type', 'article');
                $this->pageConfig->setMetadata('og:title', $metaTitle);
                $this->pageConfig->setMetadata('og:description', $description);
                $this->pageConfig->setMetadata('og:image', $category->getThumbnailUrl());
                
                if ($breadcrumbsBlock) {
                    $breadcrumbsBlock->addCrumb(
                        'home',
                        [
                            'label' => __('Home'),
                            'title' => __('Go to Home Page'),
                            'link' => $this->_storeManager->getStore()->getBaseUrl()
                        ]
                    );
                    $this->addPathToBreadcrumbs($category, $breadcrumbsBlock);
                }
                $category->setData('layout_prepared', true);
            }
        }
        
        return $this;
    }
    
    public function addPathToBreadcrumbs($category, $breadcrumbsBlock) {
        if ($category->getId()) {
            $categoryId = $category->getId();
            $path = explode('/', $category->getPath());
            if (count($path)) {
                $storeId = $this->_storeManager->getStore()->getId();
                $excludes = [$categoryId, $this->_helper->getLookbookRootCategoryId()];
                $collection = $category->getCollection()->clear()
                    ->addAttributeToFilter('is_active', 1)
                    ->addFieldToFilter('entity_id', ['in' => $path])
                    ->addFieldToFilter('entity_id', ['nin' => $excludes])
                    ->addAttributeToSelect(['name', 'url_key']);
                $storeRootId = $this->_helper->getStoreRootCategoryId();
                foreach($path as $parentId) {
                    $crumb = [];
                    if ($parentId != $categoryId) {
                        if ($parent = $collection->getItemById($parentId)) {
                            if ($parentId == $storeRootId) {
                                $link = $this->_helper->getCategoryBasedUrl();
                            } else {
                                $link = $this->_helper->getCategoryUrl($parent);
                            }
                            $breadcrumbsBlock->addCrumb(
                                'category_' . $parentId,
                                [
                                    'label' => $parent->getData('name'),
                                    'title' => $parent->getData('name'),
                                    'link' => $link
                                ]
                            );
                        }
                    } else {
                        $breadcrumbsBlock->addCrumb(
                            'category_' . $categoryId,
                            [
                                'label' => $category->getData('name'),
                                'title' => $category->getData('name')
                            ]
                        );
                    }
                }
            }
        }
    }
    
    public function getCurrentCategory()
    {
        if ($this->_category === null) {
            $this->_category = $this->_coreRegistry->registry('lookbook_category');
        }
        return $this->_category;
    }
    
    public function getCurrentObject()
    {
        return $this->getCurrentCategory();
    }
    
    public function getIdentities()
    {
        return $this->getCurrentCategory()->getIdentities();
    }
    
    public function getObjectUrl($category = null)
    {
        if ($category === null) {
            $category = $this->getCurrentCategory();
        }
        return $this->_helper->getCategoryUrl($category);
    }
}