<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * @method MageWorx\SearchSuiteAutocomplete\Block\Autocomplete\Product
 */

namespace MageWorx\SearchSuiteAutocomplete\Block\Autocomplete;

use \Magento\Catalog\Helper\Product as CatalogProductHelper;
use \Magento\Catalog\Block\Product\ReviewRendererInterface;
use \Magento\Framework\Stdlib\StringUtils;
use \Magento\Framework\Url\Helper\Data as UrlHelper;
use \Magento\Framework\Data\Form\FormKey;
use \Magento\Catalog\Block\Product\Context as ProductContext;

/**
 * Product class for autocomplete data
 *
 * @package MageWorx\SearchSuiteAutocomplete\Block\Autocomplete
 * @method Product setProduct(\Magento\Catalog\Model\Product $product)
 */
class Product extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Product constructor.
     *
     * @param CatalogProductHelper $catalogHelperProduct
     * @param StringUtils $string
     * @param UrlHelper $urlHelper
     * @param FormKey $formKey
     * @param ProductContext $context
     * @param array $data
     */
    public function __construct(
        CatalogProductHelper $catalogHelperProduct,
        StringUtils $string,
        UrlHelper $urlHelper,
        FormKey $formKey,
        ProductContext $context,
        array $data = []
    ) {
    
        $this->catalogHelperProduct = $catalogHelperProduct;
        $this->string = $string;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;

        parent::__construct($context, $data);
    }

    /**
     * Retrieve product name
     *
     * @return string
     */
    public function getName()
    {
        return html_entity_decode($this->getProduct()->getName());
    }

    /**
     * Retrieve product sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * Retrieve product small image url
     *
     * @return bool|string
     */
    public function getSmallImage()
    {
        return $this->catalogHelperProduct->getSmallImageUrl($this->getProduct());
    }

    /**
     * Retrieve product reviews rating html
     *
     * @return string
     */
    public function getReviewsRating()
    {
        return $this->getReviewsSummaryHtml(
            $this->getProduct(),
            ReviewRendererInterface::SHORT_VIEW,
            true
        );
    }

    /**
     * Retrieve product short description
     *
     * @return string
     */
    public function getShortDescription()
    {
        $shortDescription = $this->getProduct()->getShortDescription();

        return $this->cropDescription($shortDescription);
    }

    /**
     * Retrieve product description
     *
     * @return string
     */
    public function getDescription()
    {
        $description = $this->getProduct()->getDescription();

        return $this->cropDescription($description);
    }

    /**
     * Crop description to 50 symbols
     *
     * @param string|html $data
     * @return string
     */
    protected function cropDescription($data)
    {
        if (!$data) {
            return '';
        }

        $data = strip_tags($data);
        $data = (strlen($data) > 50) ? $this->string->substr($data, 0, 50) . '...' : $data;

        return $data;
    }

    /**
     * Retrieve product price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->getProductPrice($this->getProduct(), \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
    }

    /**
     * Return HTML block with tier price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();
        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                $priceType,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * Retrieve price render block
     *
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->_layout->createBlock(
            'Magento\Framework\Pricing\Render',
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

    /**
     * Retrieve product url
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->getProductUrl($this->getProduct());
    }

    /**
     * Retrieve product add to cart data
     *
     * @return array
     */
    public function getAddToCartData()
    {
        $formUrl = $this->getAddToCartUrl($this->getProduct(), ['searchsuiteautocomplete' => true]);
        $productId = $this->getProduct()->getEntityId();
        $paramNameUrlEncoded = \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED;
        $urlEncoded = $this->urlHelper->getEncodedUrl($formUrl);
        $formKey = $this->formKey->getFormKey();

        $addToCartData = [
            'formUrl' => $formUrl,
            'productId' => $productId,
            'paramNameUrlEncoded' => $paramNameUrlEncoded,
            'urlEncoded' => $urlEncoded,
            'formKey' => $formKey
        ];

        return $addToCartData;
    }
}
