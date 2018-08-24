<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Controller\Index;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class View extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog session
     *
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * Catalog design
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    protected $_attributeCode;
    
    protected $_brandFactory;
    
    protected $_urlManager;
    
    protected $_helper;
    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Design $catalogDesign,
        //\Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory,
        \Codazon\Shopbybrandpro\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogDesign = $catalogDesign;
        //$this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->_brandFactory = $brandFactory;
        $this->_attributeCode = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
			->getValue('codazon_shopbybrand/general/attribute_code');
        $this->_helper = $helper;
        $this->_urlManager = $context->getUrl();
    }
    
    protected function _initBrand($optionId)
    {
        $brandModel = $this->_brandFactory->create();
        $brandModel->setStoreId($this->_storeManager->getStore()->getId())->setOptionId($optionId)->load(null);
        $brandModel->setUrl($this->_helper->getBrandPageUrl($brandModel));
        $brandModel->setThumbnail($this->_helper->getBrandImage($brandModel, 'brand_thumbnail', ['width' => 400, 'height' => 400]));
        $brandModel->setAttributeCode($this->_attributeCode);
        return $brandModel;
    }
    
    /**
     * Initialize requested category object
     *
     * @return \Magento\Catalog\Model\Category
     */

    protected function _initCategory()
    {
        
        $optionId = (int)$this->getRequest()->getParam($this->_attributeCode, false);
        $categoryId = $this->_storeManager->getStore()->getRootCategoryId();//(int)$this->getRequest()->getParam('id', $this->_storeManager->getStore()->getRootCategoryId());
        if (!$optionId) {
            return false;
        }
        
        $brand = $this->_initBrand($optionId); 
        if($brand){
			$this->_coreRegistry->register('current_brand', $brand);
		}
        
        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        // if (!$this->_objectManager->get('Magento\Catalog\Helper\Category')->canShow($category)) {
            // return false;
        // }
        //$this->_catalogSession->setLastVisitedCategoryId($category->getId());
        
        /* get all products of children categories */
        $category->setIsAnchor(true);
        
        $this->_coreRegistry->register('current_category', $category);
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category, 'controller_action' => $this]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        $category = $this->_initCategory();
        if ($category) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            /* if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            } */

            //$this->_catalogSession->setLastViewedCategoryId($category->getId());

            $page = $this->resultPageFactory->create();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $page->getConfig()->setPageLayout($settings->getPageLayout());
            }

            $hasChildren = $category->hasChildren();
            $type = $hasChildren ? 'layered' : 'default_without_children';

            if (!$hasChildren) {
                // Two levels removed from parent.  Need to add default page type.
                $parentType = strtok($type, '_');
                $page->addPageLayoutHandles(['type' => $parentType]);
            }
            $page->addPageLayoutHandles(['type' => $type, 'id' => $category->getId()]);

            // apply custom layout update once layout is loaded
            // $layoutUpdates = $settings->getLayoutUpdates();
            // if ($layoutUpdates && is_array($layoutUpdates)) {
                // foreach ($layoutUpdates as $layoutUpdate) {
                    // $page->addUpdate($layoutUpdate);
                    // $page->addPageLayoutHandles(['layout_update' => md5($layoutUpdate)]);
                // }
            // }

            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($category))
                ->addBodyClass('category-' . $category->getUrlKey())
                ->addBodyClass('catalog-category-view');

            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}