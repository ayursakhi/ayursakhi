<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Category;

use Magento\Framework\View\Result\PageFactory;
use Codazon\Lookbookpro\Model\LookbookCategoryFactory;


class View extends \Magento\Framework\App\Action\Action
{
    protected $coreRegistry;
    
    protected $storeManager;
    
    protected $resultPageFactory;
    
    protected $resultForwardFactory;
        
    protected $lookbookFactory;
    
    protected $categoryFactory;
    
    protected $helper;
    
    protected $scopeConfig;
    
    protected $storeId;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $categoryFactory,
        \Codazon\Lookbookpro\Model\LookbookFactory $lookbookFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Codazon\Lookbookpro\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->lookbookFactory = $lookbookFactory;
        $this->categoryFactory = $categoryFactory;
        $this->helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->storeId = $this->storeManager->getStore()->getId();
    }
    
    protected function getAllLookPageInformation()
    {
        return [
            'name'                  => $this->scopeConfig->getValue('codazon_lookbook/all_lookbooks_page/name', 'store'),
            'description'           => $this->scopeConfig->getValue('codazon_lookbook/all_lookbooks_page/description', 'store'),
            'meta_title'            => $this->scopeConfig->getValue('codazon_lookbook/all_lookbooks_page/meta_title', 'store'),
            'meta_keywords'         => $this->scopeConfig->getValue('codazon_lookbook/all_lookbooks_page/meta_keywords', 'store'),
            'meta_description'      => $this->scopeConfig->getValue('codazon_lookbook/all_lookbooks_page/meta_description', 'store'),
        ];
    }
    
    protected function addDefaultInformation($category)
    {
        $category->addData([
            'thumbnail_url' => $this->helper->getCategoryThumbnailUrl($category, 526, 374),
            'url'           => $this->helper->getCategoryUrl($category)
        ]);
    }
    
    protected function _initCategory()
    {
        $storeRootId = $this->helper->getStoreRootCategoryId();
        $categoryId = $this->getRequest()->getParam('id', $storeRootId);
        $category = $this->categoryFactory->create();
        $category->setStoreId($this->storeId);
        
        
        if (!$categoryId) {
            $categoryId = $storeRootId;
        }
        
        if ($categoryId) {
            $category->load($categoryId);
            $this->addDefaultInformation($category);
        } else {
            $this->getAllLookPageInformation();
        }
        $recursive = $category->getData('is_anchor');
        $lookbooks = $this->helper->getLoobookByCategory($category, $this->storeId, $recursive);
        $category->setLookbookCollection($lookbooks);
        $this->coreRegistry->register('lookbook_category', $category);
        return $category;
    }    
     
    public function execute()
    {
        $category = $this->_initCategory();
        $page = $this->resultPageFactory->create();
        if ($category->getUrlKey()) {
            $urlKey = $category->getUrlKey();
        } else {
            $urlKey = 'root';
        }
        $page->getConfig()->addBodyClass('lookbook-category')->addBodyClass('lookbook-category-' . $urlKey);
        return $page;
    }
}