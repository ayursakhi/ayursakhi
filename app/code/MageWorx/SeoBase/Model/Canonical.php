<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model;

abstract class Canonical implements \MageWorx\SeoBase\Model\CanonicalInterface
{
    /**
     * @var \MageWorx\SeoBase\Helper\Data
     */
    protected $helperData;

    /**
     * @var \MageWorx\SeoBase\Helper\Url
     */
    protected $helperUrl;

    /**
     * @var \MageWorx\SeoBase\Helper\StoreUrl
     */
    protected $helperStoreUrl;

    /**
     * @var string
     */
    protected $fullActionName;

    /**
     * @return string
     */
    abstract public function getCanonicalUrl();

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url  $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        $fullActionName
    ) {
        $this->helperData     = $helperData;
        $this->helperUrl      = $helperUrl;
        $this->helperStoreUrl = $helperStoreUrl;
        $this->fullActionName = $fullActionName;
    }

    /**
     * Crop or add trailing slash
     *
     * @param string $url
     * @param int|null $storeId
     * @param bool $isHomePage
     * @return string
     */
    public function trailingSlash($url, $storeId = null, $isHomePage = false)
    {
        return $this->helperStoreUrl->trailingSlash($url, $storeId, $isHomePage);
    }

    /**
     * Check if cancel adding canonical URL by config settings
     *
     * @return bool
     */
    protected function isCancelCanonical()
    {
        if ($this->helperData->isCanonicalUrlEnabled()) {
            if ($this->fullActionName == 'mageworx_landingpagespro_landingpage_view') {
                return true;
            }
            return in_array($this->fullActionName, $this->helperData->getCanonicalIgnorePages());
        }
        return true;
    }

    /**
     * Prepare ULR to output
     *
     * @param string $url
     * @return string
     */
    public function renderUrl($url)
    {
        return $this->helperUrl->escapeUrl($this->trailingSlash($url));
    }
}
