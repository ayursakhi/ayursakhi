<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Canonical;

use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\CrossDomainFactory as CrossDomainFactory;
use MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\AssociatedFactory as AssociatedFactory;

/**
 * SEO Base product canonical URL model
 */
class Product extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\CrossDomainFactory
     */
    protected $crossDomainFactory;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Product\AssociatedFactory
     */
    protected $associatedFactory;

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param \Magento\Framework\Registry $registry
     * @param CrossDomainFactory $crossDomainFactory
     * @param AssociatedFactory $associatedFactory
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url  $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        \Magento\Framework\Registry   $registry,
        CrossDomainFactory $crossDomainFactory,
        AssociatedFactory  $associatedFactory,
        $fullActionName
    ) {
        $this->registry   = $registry;
        $this->crossDomainFactory = $crossDomainFactory;
        $this->associatedFactory  = $associatedFactory;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $fullActionName);
    }


    /**
     * Retrieve product canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }

        $product = $this->registry->registry('current_product');
        if (!$product) {
            return null;
        }

        $crossDomainStoreByProduct = $this->getCrossDomainStoreId($product->getCrossDomainStore());
        $crossDomainStoreByConfig  = $this->getCrossDomainStoreId($this->helperData->getCrossDomainStore());

        $crossDomainUrlByProduct   = $product->getCrossDomainUrl();
        $crossDomainUrlByConfig    = $this->helperData->getCrossDomainUrl();

        if ($crossDomainStoreByProduct) {
            $crossDomainProduct = $this->crossDomainFactory->create()
                ->getCrossDomainData($product->getId(), $crossDomainStoreByProduct, null);
            if (is_object($crossDomainProduct)) {
                $canonicalUrl = $crossDomainProduct->getUrl();
            }
        } elseif ($crossDomainUrlByProduct) {
            $canonicalUrl = $this->getCrossDomainUrlByCustomUrl(
                $crossDomainUrlByProduct,
                $this->getProductUrl($product)
            );
        } elseif ($crossDomainStoreByConfig) {
            $crossDomainProduct = $this->crossDomainFactory->create()
                ->getCrossDomainData($product->getId(), $crossDomainStoreByConfig, null);
            if (is_object($crossDomainProduct)) {
                $canonicalUrl = $crossDomainProduct->getUrl();
            }
        } elseif ($crossDomainUrlByConfig) {
            $canonicalUrl = $this->getCrossDomainUrlByCustomUrl(
                $crossDomainUrlByConfig,
                $this->getProductUrl($product)
            );
        }

        $associatedProductTypes = $this->helperData->getAssociatedProductTypesAsArray();
        if (empty($canonicalUrl) && is_array($associatedProductTypes) && !empty($associatedProductTypes)) {
            $associatedProduct = $this->associatedFactory->create()
                ->getAssociatedData($product->getId(), $associatedProductTypes, $product->getStoreId());
            if (is_object($associatedProduct)) {
                $canonicalUrl = $associatedProduct->getUrl();
            }
        }

        if (empty($canonicalUrl)) {
            $canonicalUrl = $this->getProductUrl($product);
        }

        return $this->renderUrl($canonicalUrl);
    }

    /**
     *
     * @param string $product
     */
    protected function getProductUrl($product)
    {
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }

    /**
     *  Retrieve cross domain store ID
     *
     * @param int $storeId
     * @return int|false
     */
    protected function getCrossDomainStoreId($storeId)
    {
        if (!$storeId) {
            return false;
        }
        if (!$this->helperStoreUrl->isActiveStore($storeId)) {
            return false;
        }
        if ($this->helperStoreUrl->getCurrentStoreId() == $storeId) {
            return false;
        }
        return $storeId;
    }

    /**
     * Retrieve cross domain URL
     *
     * @param string $crossDomainBaseUrl
     * @param string $productUrl
     * @return string
     */
    protected function getCrossDomainUrlByCustomUrl($crossDomainBaseUrl, $productUrl)
    {
        $crossDomainBaseUrlTrim = rtrim(trim($crossDomainBaseUrl), '/') . '/';
        $storeBaseUrl = $this->helperStoreUrl->getStoreBaseUrl();
        return str_replace($storeBaseUrl, $crossDomainBaseUrlTrim, $productUrl);
    }
}
