<?php
/**
 * Copyright © 2015 Codazon . All rights reserved.
 */
namespace Codazon\ThemeOptions\Helper;
use Magento\Framework\App\Filesystem\DirectoryList;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	protected $_scopeConfig;
	protected $_pageConfig;
	/**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Codazon\ThemeOptions\Framework\App\Config $themeConfig,
		\Magento\Framework\App\Config $scopeConfig,
		\Codazon\ThemeOptions\Model\ConfigFactory $configFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Page\Config $pageConfig,
		\Magento\Catalog\Model\ProductFactory $productLoader,
		\Magento\Catalog\Helper\Image $imageHelper
	) {
		parent::__construct($context);
		$this->_storeManager = $storeManager;
		$this->_scopeConfig = $scopeConfig;
		$this->_themeConfig = $themeConfig;		
		$this->_pageConfig = $pageConfig;
		$this->_configFactory = $configFactory;
		$this->_productLoader = $productLoader;
		$this->_imageHelper = $imageHelper;
		$this->_storeId = $storeManager->getStore()->getId();
	}
	
	public function getConfig($fullPath){		
		return $this->_themeConfig->getValue($fullPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $this->_storeId);
	}
	public function getThemeOptionsLabel(){
		return $this->_scopeConfig->getValue('themeoptions/general/label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->_storeId);	
	}
	public function getBaseUrl(){		
		return $this->_storeManager->getStore()->getBaseUrl();
	}

	public function getMediaUrl(){
		return $this->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA ); 
	}
		
	public function getPageColumns(){
	      return $this->_pageConfig->getPageLayout();
	}
	
	public function setBodyClass($class){			
		$this->_pageConfig->addBodyClass($class);		
	}

    public function isHomePage()
    {
        $currentUrl = $this->_urlBuilder->getUrl('', ['_current' => true]);
        $urlRewrite = $this->_urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);        
        return $currentUrl == $urlRewrite;
    }

    public function getImageGallery($product_id){
    	ini_set('display_errors',1);
		$product = $this->_productLoader->create()->load($product_id);
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            foreach ($images as $image) {
                /* @var \Magento\Framework\DataObject $image */
                $image->setData(
                    'small_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_small')
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'medium_image_url',
                    $this->_imageHelper->init($product, 'category_page_list')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
                $image->setData(
                    'large_image_url',
                    $this->_imageHelper->init($product, 'product_page_image_large')
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->getUrl()
                );
            }
        }
        $imagesItems = [];
        foreach ($images as $image) {
            $imagesItems[] = [
                'thumb' => $image->getData('small_image_url'),
                'img' => $image->getData('medium_image_url'),
                'caption' => $image->getLabel(),
                'position' => $image->getPosition()
            ];
        }
        if (empty($imagesItems)) {
            $imagesItems[] = [
                'thumb' => $this->_imageHelper->getDefaultPlaceholderUrl('thumbnail'),
                'img' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'full' => $this->_imageHelper->getDefaultPlaceholderUrl('image'),
                'caption' => '',
                'position' => '0',
                'isMain' => true,
            ];
        }
        return $imagesItems;
    }
}