<?php
/**
 * Copyright © 2015 Codazon . All rights reserved.
 */
namespace Codazon\ProductFilter\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	 /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;
	/**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     */
     
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,   
		\Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
		\Magento\Catalog\Block\Product\Context $productContext,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	) {
		$this->productTypeConfig = $productTypeConfig;
		$this->stockRegistry = $productContext->getStockRegistry();
        $this->_localeFormat = $localeFormat;
        $this->_jsonEncoder = $jsonEncoder;
        $this->priceCurrency = $priceCurrency;
		parent::__construct($context);
	}

    public function hasOptions()
    {
        if ($this->_product->getTypeInstance()->hasOptions($this->_product)) {
            return true;
        }
        return false;
    }

    public function getJsonConfig($product){
        $this->_product = $product;
        if (!$this->hasOptions()) {
            $config = [
                'productId' => $product->getId(),
                'priceFormat' => $this->_localeFormat->getPriceFormat()
                ];
            return $this->_jsonEncoder->encode($config);
        }

        $tierPrices = [];
        $tierPricesList = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
        foreach ($tierPricesList as $tierPrice) {
            $tierPrices[] = $this->priceCurrency->convert($tierPrice['price']->getValue());
        }
        $config = [
            'productId' => $product->getId(),
            'priceFormat' => $this->_localeFormat->getPriceFormat(),
            'prices' => [
                'oldPrice' => [
                    'amount' => $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(),
                    'adjustments' => []
                ],
                'basePrice' => [
                    'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount(),
                    'adjustments' => []
                ],
                'finalPrice' => [
                    'amount' => $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),
                    'adjustments' => []
                ]
            ],
            'idSuffix' => '_clone',
            'tierPrices' => $tierPrices
        ];

        $responseObject = new \Magento\Framework\DataObject();
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option => $value) {
                $config[$option] = $value;
            }
        }

        return $this->_jsonEncoder->encode($config);
    }
	
	/**
     * Gets minimal sales quantity
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return int|null
     */
    public function getMinimalQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minSaleQty = $stockItem->getMinSaleQty();
        return $minSaleQty > 0 ? $minSaleQty : null;
    }
    
    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|\Magento\Catalog\Model\Product $product
     * @return int|float
     */
    public function getProductDefaultQty($product = null)
    {
        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }
	
	/**
     * Check whether quantity field should be rendered
     *
     * @return bool
     */
    public function shouldRenderQuantity($product)
    {
        return !$this->productTypeConfig->isProductSet($product->getTypeId());
    }
    
    /**
     * Get Validation Rules for Quantity field
     *
     * @return array
     */
    public function getQuantityValidators()
    {
        $validators = [];
        $validators['required-number'] = true;
        return $validators;
    }
	
}
