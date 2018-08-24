<?php
/**
 * Copyright © 2015 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\AjaxLayeredNav\Controller\Load;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class Data extends \Magento\Catalog\Controller\Category\View
{
	protected $viewHelper;
    protected $resultForwardFactory;
    protected $resultPageFactory;
	protected $template;
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($context, 
            $catalogDesign, 
            $catalogSession, 
            $coreRegistry, 
            $storeManager,
            $categoryUrlPathGenerator,
            $resultPageFactory,
            $resultForwardFactory,
            $layerResolver,
            $categoryRepository
        );
        $this->_storeManager = $storeManager;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->resultFactory = $context->getResultFactory();
    }
    
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('catid', false);
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        if (!$this->_objectManager->get('Magento\Catalog\Helper\Category')->canShow($category)) {
            return false;
        }
        $this->_catalogSession->setLastViewedCategoryId($category->getId());
        $this->_coreRegistry->register('current_category', $category);

        return $category;
    }
    
    public function execute()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            // is Ajax request
            $category = $this->_initCategory();
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
            return $resultLayout;
        }else{
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
