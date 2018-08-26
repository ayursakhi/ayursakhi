<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoUrls\Plugin\Pager;

use Magento\Framework\View\Element\Template;

class AroundGetPagerUrl
{
    /**
     * @var \MageWorx\SeoUrls\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Seo Url Builder
     *
     * @var \MageWorx\SeoUrls\Helper\SeoUrlBuilder
     */
    protected $seoUrlBuilder;

    /**
     * AroundGetPagerUrl constructor.
     * @param \MageWorx\SeoUrls\Helper\Data $helperData
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \MageWorx\SeoUrls\Helper\SeoUrlBuilder $seoUrlBuilder
     */
    public function __construct(
        \MageWorx\SeoUrls\Helper\Data $helperData,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageWorx\SeoUrls\Helper\SeoUrlBuilder $seoUrlBuilder
    ) {
        $this->helperData = $helperData;
        $this->urlHelper  = $urlHelper;
        $this->urlBuilder = $urlBuilder;
        $this->seoUrlBuilder = $seoUrlBuilder;
    }

    /**
     * @param Template $subject
     * @param $proceed
     * @param array $params
     * @return string
     */
    public function aroundGetPagerUrl(Template $subject, $proceed, $params = [])
    {
        return $this->seoUrlBuilder->getPagerUrl($proceed($params), $params);
    }
}
