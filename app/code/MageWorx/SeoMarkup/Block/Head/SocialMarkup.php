<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head;

abstract class SocialMarkup extends \MageWorx\SeoMarkup\Block\Head
{
    /**
     * @var \MageWorx\SeoMarkup\Helper\Website
     */
    protected $helperWebsite;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * SocialMarkup constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Website $helperWebsite
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Website $helperWebsite,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data
    ) {
        $this->registry           = $registry;
        $this->helperWebsite  = $helperWebsite;
        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->getMarkupHtml();
    }

    /**
     * Retrieve facebook logo
     *
     * @return string
     */
    public function getOgImageUrl()
    {
        $folderName = 'og_image';
        $storeConfig = $this->helperWebsite->getOgImage();
        $faviconFile = $this->_storeManager->getStore()->getBaseUrl('media') . $folderName . '/' . $storeConfig;
        if ($storeConfig !== '') {
            return $faviconFile;
        }
        return false;

    }
}
