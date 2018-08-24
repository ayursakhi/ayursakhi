<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\AjaxLayeredNav\Controller\Load;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class DataBrand extends \Magento\Framework\App\Action\Action
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
        $this->resultFactory = $context->getResultFactory();
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
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            $category = $this->_initCategory();
            if ($category) {
                $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
                $settings = $this->_catalogDesign->getDesignSettings($category);

                // apply custom design
                /* if ($settings->getCustomDesign()) {
                    $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
                } */

                //$this->_catalogSession->setLastViewedCategoryId($category->getId());

                $layout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
                return $layout;
            }
        } else {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}