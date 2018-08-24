<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteAutocomplete\Block\Autocomplete;

use \MageWorx\SearchSuiteAutocomplete\Block\Product as ProductBlock;
use \Magento\Catalog\Helper\Product as CatalogProductHelper;
use \Magento\Catalog\Block\Product\ReviewRendererInterface;
use \Magento\Framework\Stdlib\StringUtils;
use \Magento\Framework\Url\Helper\Data as UrlHelper;
use \Magento\Framework\Data\Form\FormKey;

/**
 * ProductAgregator class for autocomplete data
 *
 * @method Product setProduct(\Magento\Catalog\Model\Product $product)
 */
class ProductAgregator extends \Magento\Framework\DataObject
{
    /**
     * @var \MageWorx\SearchSuiteAutocomplete\Block\Product
     */
    protected $productBlock;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
    
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
        ProductBlock $productBlock,
        CatalogProductHelper $catalogHelperProduct,
        StringUtils $string,
        UrlHelper $urlHelper,
        FormKey $formKey
    ) {
        $this->productBlock = $productBlock;
        $this->catalogHelperProduct = $catalogHelperProduct;
        $this->string = $string;
        $this->urlHelper = $urlHelper;
        $this->formKey = $formKey;
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
        return $this->productBlock->getReviewsSummaryHtml(
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
        return $this->productBlock->getProductPrice($this->getProduct(), \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE);
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
        return $this->productBlock->getProductUrl($this->getProduct());
    }

    /**
     * Retrieve product add to cart data
     *
     * @return array
     */
    public function getAddToCartData()
    {
        $formUrl = $this->productBlock->getAddToCartUrl($this->getProduct(), ['mageworx_searchsuiteautocomplete' => true]);
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
