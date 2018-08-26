<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Model;

/**
 * Template Filter Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Filter extends \Magento\Cms\Model\Template\Filter
{
    /**
     * \MageWorx\SeoCrossLinks\Helper\XFunction
     */
    protected $xfunction;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Variable\Model\VariableFactory $coreVariableFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\UrlInterface $urlModel
     * @param \Pelago\Emogrifier $emogrifier
     * @param \Magento\Email\Model\Source\Variables $configVariables
     * @param \MageWorx\SeoCrossLinks\Helper\XFunction $xfunction
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Variable\Model\VariableFactory $coreVariableFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\UrlInterface $urlModel,
        \Pelago\Emogrifier $emogrifier,
        \Magento\Email\Model\Source\Variables $configVariables,
        \MageWorx\SeoCrossLinks\Helper\XFunction $xfunction
    ) {
        $this->xfunction = $xfunction;
        parent::__construct(
            $string,
            $logger,
            $escaper,
            $assetRepo,
            $scopeConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory,
            $appState,
            $urlModel,
            $emogrifier,
            $configVariables
        );
    }

    /**
     * Replace widget codes to hash.
     *
     * @param string $value
     * @param array $replacedPairs
     * @return string
     */
    public function replace($value, &$replacedPairs)
    {
        // "depend" and "if" operands should be first
        foreach (array(
            self::CONSTRUCTION_DEPEND_PATTERN,
            self::CONSTRUCTION_IF_PATTERN,
            ) as $pattern) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $replacedValue = $this->_getRandomValue();
                    $replacedPairs[$replacedValue] = $construction[0];
                    $value = $this->xfunction->strReplaceOnce($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $replacedValue = $this->_getRandomValue();
                $replacedPairs[$replacedValue] = $construction[0];
                $value = $this->xfunction->strReplaceOnce($construction[0], $replacedValue, $value);
            }
        }

        return $value;
    }

    /**
     *
     * @return string
     */
    protected function _getRandomValue()
    {
        return substr(md5(rand()), 0, 9);
    }
}
