<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Canonical;

/**
 * SEO Base non-specific pages canonical URL model
 */
class Simple extends \MageWorx\SeoBase\Model\Canonical
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param \Magento\Framework\UrlInterface $url
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url  $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        \Magento\Framework\UrlInterface $url,
        $fullActionName
    ) {
        $this->url = $url;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $fullActionName);
    }

    /**
     * Retrieve non-specific pages canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }
        $currentUrl = $this->url->getCurrentUrl();
        $url = $this->helperUrl->deleteAllParametrsFromUrl($currentUrl);
        return $this->renderUrl($url);
    }
}
