<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head\Json;

class Product extends \MageWorx\SeoMarkup\Block\Head\Json
{
    const IN_STOCK     = 'http://schema.org/InStock';
    const OUT_OF_STOCK = 'http://schema.org/OutOfStock';
    const OFFER        = 'http://schema.org/Offer';

    /**
     *
     * @var \MageWorx\SeoMarkup\Helper\Product
     */
    protected $helperProduct;

    /**
     *
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperDataProvider;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     *
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Product $helperProduct
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Product $helperProduct,
        \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry            = $registry;
        $this->helperProduct       = $helperProduct;
        $this->helperDataProvider  = $dataProviderProduct;
        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html            = '';
        $productJsonData = [];

        if ($this->helperProduct->isRsEnabled()) {
            $productJsonData[] = $this->getJsonProductData();
        }

        if ($this->helperProduct->isGaEnabled()) {
            $productJsonData[] = $this->getGoogleAssistantJsonData();
        }

        $productJson = !empty($productJsonData) ? json_encode($productJsonData) : '';

        if ($productJson) {
            $html .= '<script type="application/ld+json">' . $productJson . '</script>';
        }

        return $html;
    }

    /**
     *
     * @return array
     */
    protected function getJsonProductData()
    {
        $product = $this->registry->registry('current_product');

        if (!$product) {
            return [];
        }

        $this->_product = $product;

        $data = [];
        $data['@context']    = 'http://schema.org';
        $data['@type']       = 'Product';
        $data['name']        = $this->_product->getName();
        $data['description'] = $this->helperDataProvider->getDescriptionValue($this->_product);
        $data['image']       = $this->helperDataProvider->getProductImageUrl($this->_product);
        $data['offers']      = $this->getOfferData();

        if (!$data['offers']['price']) {
            return false;
        }

        $aggregateRatingData = $this->helperDataProvider->getAggregateRatingData($this->_product, false);

        if (!empty($aggregateRatingData)) {
            $aggregateRatingData['@type'] = 'AggregateRating';
            $data['aggregateRating'] = $aggregateRatingData;
        }

        $color = $this->helperDataProvider->getColorValue($this->_product);
        if ($color) {
            $data['color'] = $color;
        }

        $brand = $this->helperDataProvider->getBrandValue($this->_product);
        if ($brand) {
            $data['brand'] = $brand;
        }

        $manufacturer = $this->helperDataProvider->getManufacturerValue($this->_product);
        if ($manufacturer) {
            $data['manufacturer'] = $manufacturer;
        }

        $model = $this->helperDataProvider->getModelValue($this->_product);
        if ($model) {
            $data['model'] = $model;
        }

        $gtin =  $this->helperDataProvider->getGtinData($this->_product);
        if (!empty($gtin['gtinType']) && !empty($gtin['gtinValue'])) {
            $data[$gtin['gtinType']] = $gtin['gtinValue'];
        }

        $skuValue = $this->helperDataProvider->getSkuValue($this->_product);
        if ($skuValue) {
            $data['sku'] = $skuValue;
        }

        $weightValue = $this->helperDataProvider->getWeightValue($this->_product);
        if ($weightValue) {
            $data['weight'] = $weightValue;
        }

        $categoryName = $this->helperDataProvider->getCategoryValue($this->_product);
        if ($categoryName) {
            $data['category'] = $categoryName;
        }

        $customProperties = $this->helperProduct->getCustomProperties();

        if ($customProperties) {
            foreach ($customProperties as $propertyName => $propertyValue) {
                if (!$propertyName || !$propertyValue) {
                    continue;
                }
                $value = $this->helperDataProvider->getCustomPropertyValue($product, $propertyValue);
                if ($value) {
                    $data[$propertyName] = $value;
                }
            }
        }

        return $data;
    }

    /**
     *
     * @return array
     */
    protected function getOfferData()
    {
        $data          = [];
        $data['@type'] = self::OFFER;
        $data['price'] = $this->_product->getFinalPrice();
        $data['priceCurrency'] = $this->helperDataProvider->getCurrentCurrencyCode();

        if ($this->helperDataProvider->getAvailability($this->_product)) {
            $data['availability'] = self::IN_STOCK;
        } else {
            $data['availability'] = self::OUT_OF_STOCK;
        }

        $condition = $this->helperDataProvider->getConditionValue($this->_product);
        if ($condition) {
            $data['itemCondition'] = $condition;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getGoogleAssistantJsonData()
    {
        $data['@context']         = 'http://schema.org/';
        $data['@type']            = 'WebPage';
        $speakable                = [];
        $speakable['@type']       = 'SpeakableSpecification';
        $speakable['cssSelector'] = explode(',', $this->helperProduct->getGaCssSelectors());
        $speakable['xpath']       = ['/html/head/title'];
        $data['speakable']        = $speakable;
        return $data;
    }
}
