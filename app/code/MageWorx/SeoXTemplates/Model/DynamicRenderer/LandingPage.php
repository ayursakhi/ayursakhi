<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DynamicRenderer;

class LandingPage
{
    /**
     * @var string
     */
    protected $isConvertedTitle;

    /**
     * @var string
     */
    protected $isConvertedMetaDescription;

    /**
     * @var string
     */
    protected $isConvertedTexts;

    /**
     * @var \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaTitle
     */
    protected $metaTitleConverter;

    /**
     * @var \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaDescription
     */
    protected $metaDescriptionConverter;

    /**
     * @var \MageWorx\SeoXTemplates\Model\Converter\LandingPage\Text
     */
    protected $textConverter;

    /**
     * @var \Zend\Filter\StripTags
     */
    protected $stripTags;
    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     *
     * @param \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaTitle $metaTitleConverter ,
     * @param \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaDescription $metaDescriptionConverter ,
     * @param \MageWorx\SeoXTemplates\Model\Converter\LandingPage\Description $descriptionConverter
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaTitle $metaTitleConverter,
        \MageWorx\SeoXTemplates\Model\Converter\LandingPage\MetaDescription $metaDescriptionConverter,
        \MageWorx\SeoXTemplates\Model\Converter\LandingPage\Text $textConverter,
        \Zend\Filter\StripTags $stripTags,
        \Magento\Framework\View\Page\Config $pageConfig
    ) {
        $this->storeManager             = $storeManager;
        $this->metaTitleConverter       = $metaTitleConverter;
        $this->metaDescriptionConverter = $metaDescriptionConverter;
        $this->textConverter            = $textConverter;
        $this->stripTags                = $stripTags;
        $this->pageConfig               = $pageConfig;
    }

    /**
     * @param $landingPage
     * @return bool
     */
    public function modifyLandingPageTitle($landingPage)
    {
        if ($this->isConvertedTitle) {
            return true;
        }

        $this->isConvertedTitle = true;
        $title                  = $this->metaTitleConverter->convert($landingPage, $landingPage->getMetaTitle(), true);

        if (!empty($title)) {
            $title = trim(htmlspecialchars(html_entity_decode($title, ENT_QUOTES, 'UTF-8')));
            if ($title) {
                $this->pageConfig->getTitle()->set($title);

                return true;
            }
        };

        return false;
    }

    /**
     * @param \Magento\Catalog\Model\LandingPage $landingPage
     * @return boolean
     */
    public function modifyLandingPageMetaDescription($landingPage)
    {
        if ($this->isConvertedMetaDescription) {
            return true;
        }

        $metaDescription = $this->metaDescriptionConverter
            ->convert($landingPage, $landingPage->getMetaDescription(), true);

        if (!empty($metaDescription)) {
            $metaDescription = htmlspecialchars(
                html_entity_decode(
                    preg_replace(
                        ['/\r?\n/', '/[ ]{2,}/'],
                        [' ', ' '],
                        $this->stripTags->filter($metaDescription)
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                )
            );
            if ($metaDescription) {
                $this->isConvertedMetaDescription = $metaDescription;
                $this->pageConfig->setDescription($metaDescription);

                return true;
            }
        }

        return false;
    }

    /**
     * @param $landingPage
     * @return bool
     */
    public function modifyLandingPageTexts($landingPage)
    {
        if ($this->isConvertedTexts) {
            return true;
        }
        $texts   = ['text_1', 'text_2', 'text_3', 'text_4'];
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($texts as $textLabel) {

            $text = $this->textConverter->convert(
                $landingPage,
                $landingPage->getStoreValue($textLabel, $storeId),
                true
            );

            if (!empty($text)) {
                $this->isConvertedTexts = $text;
                $landingPage->setStoreValue($textLabel, $text, $storeId);
            }
        }

        return true;
    }
}
