<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Lookbookpro\Block\Lookbook;

class View extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_coreRegistry;

    protected $_helper;
    
    protected $_copeConfig;
        
    protected $_mediaUrl;
    
    protected $_lookbook;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Codazon\Lookbookpro\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_storeManager = $context->getStoreManager();
		$this->_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_assetRepository = $this->_objectManager->get('Magento\Framework\View\Asset\Repository');
		$this->_helper = $helper;
		$this->_copeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $lookbook = $this->getCurrentLookbook();
        if ($lookbook) {
            if (!$lookbook->getData('layout_prepared')) {
                $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
                $title = $lookbook->getName();
                $metaTitle = $lookbook->getMetaTitle()?:$title;
                $this->pageConfig->getTitle()->set($metaTitle);
                
                $description = $lookbook->getMetaDescription()?:$lookbook->getDescription();
                if ($description) {
                    $this->pageConfig->setDescription($description);
                }
                $keywords = $lookbook->getMetaKeywords();

                if ($keywords) {
                    $this->pageConfig->setKeywords($keywords);
                }
                            
                $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
                if ($pageMainTitle) {
                    $pageMainTitle->setPageTitle($title);
                }
                
                /* facebook meta tag */
                $this->pageConfig->setMetadata('og:url', $lookbook->getUrl());
                $this->pageConfig->setMetadata('og:type', 'article');
                $this->pageConfig->setMetadata('og:title', $metaTitle);
                $this->pageConfig->setMetadata('og:description', $description);
                $this->pageConfig->setMetadata('og:image', $lookbook->getThumbnailUrl());
                
                if ($breadcrumbsBlock) {
                    $breadcrumbsBlock->addCrumb(
                        'home',
                        [
                            'label' => __('Home'),
                            'title' => __('Go to Home Page'),
                            'link' => $this->_storeManager->getStore()->getBaseUrl()
                        ]
                    );
                    
                    if ($category = $this->_coreRegistry->registry('lookbook_category')) {
                        $breadcrumbsBlock->addCrumb(
                            'all',
                            [
                                'label' => $category->getName(),
                                'title' => $category->getName(),
                                'link' => $this->_helper->getCategoryUrl($category)
                            ]
                        );
                    } else {                        
                        $breadcrumbsBlock->addCrumb(
                            'all',
                            [
                                'label' => __('All Lookbooks'),
                                'title' => __('All Lookbooks'),
                                'link' => $this->getUrl('lookbook')
                            ]
                        );
                    }
                    $breadcrumbsBlock->addCrumb(
                        'lookbook',
                        [
                            'label' => $title,
                            'title' => $title
                        ]
                    );
                }
                $lookbook->setData('layout_prepared', true);
            }
        }
        return $this;
    }
    
    public function getCurrentLookbook()
    {
        if ($this->_lookbook === null) {
            $this->_lookbook = $this->_coreRegistry->registry('current_lookbook');
        }
        return $this->_lookbook;
    }
    
    public function getCurrentObject()
    {
        return $this->getCurrentLookbook();
    }
    
    public function getIdentities()
    {
        $categoryId = $this->_helper->getStoreRootCategoryId();
        if ($category = $this->_coreRegistry->registry('lookbook_category')) {
            $categoryId = '_' . $category->getId();
        }
        return array_merge($this->getCurrentLookbook()->getIdentities(), [$categoryId]);
    }
}