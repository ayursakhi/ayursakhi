<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogRule data helper
 */
namespace Codazon\Shopbybrandpro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_urlBuilder;
    protected $_imageHelper;
    protected $_brandFactory;
    protected $_storeManager;
    protected $_attributeCode;
    
    protected $_brandProducts = [];
    protected $_brandProductCount = [];
    
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Codazon\Shopbybrandpro\Helper\Image $imageHelper,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_imageHelper = $imageHelper;
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $storeManager;
        $this->_attributeCode = $this->_scopeConfig->getValue('codazon_shopbybrand/general/attribute_code');
    }
    
    public function getStoreBrandCode() 
    {
        return $this->_attributeCode;
    }
    
    public function getUrl($path, $params = [])
    {
        return $this->_urlBuilder->getUrl($path, $params);
    }
    
    public function getBrandImage($brand, $type = 'brand_thumbnail', $options)
    {
        $brandThumb = $brand->getData($type);
        if ($type == 'brand_thumbnail') {
            if (!$brandThumb) {
                $brandThumb = 'codazon/brand/placeholder_thumbnail.jpg';
            }
        }
		if ($brandThumb) {
			if (isset($options['width']) || isset($options['height'])) {
				if(!isset($options['width'])) {
					$options['width'] = null;
                }
				if(!isset($options['height'])) {
					$options['height'] = null;
                }
				return $this->_imageHelper->init($brandThumb)
                    ->resize($options['width'], $options['height'])->__toString();
			} else {
				return $this->_mediaUrl.$brand->getData($type);
			}
		}else{
			return '';
		}
	}
    
    public function getBrandPageUrl($brandModel)
    {
		if ($brandModel->getData('brand_url_key')) {
            return $this->getUrl('brand', ['_nosid' => true]) . $brandModel->getData('brand_url_key');
        } else {
            return $this->getUrl('brand', ['_nosid' => true]) . urlencode(str_replace(' ','-',strtolower(trim($brandModel->getData('brand_label')))));
        }
	}
    
    public function getProductCount($attributeCode, $optionId)
    {
        $key = $attributeCode.'_'.$optionId;
        if (!isset($this->_brandProductCount[$key])) {
            $collection = $this->_objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
            $collection->addAttributeToSelect([$attributeCode]);
            $collection->addAttributeToFilter($attributeCode, $optionId);
            $this->_brandProductCount[$key] = $collection->count();
        }
        return $this->_brandProductCount[$key];
    }
    
    public function getBrandByProduct($product, $attributeCode)
    {
        $attrValue = (int)$product->getData($attributeCode);
        
        if (!isset($this->_brandProducts[$attrValue])) {
            $brandModel = $this->_brandFactory->create()->setStoreId($this->_storeManager->getStore()->getId())
                ->setOptionId($attrValue)
                ->load(null);
            $brandModel->setUrl($this->getBrandPageUrl($brandModel));
            $brandModel->setThumbnail($this->getBrandImage($brandModel, 'brand_thumbnail', ['width' => 150]));
            $this->_brandProducts[$attrValue] = $brandModel;
        }
		return $this->_brandProducts[$attrValue];
	}
}
