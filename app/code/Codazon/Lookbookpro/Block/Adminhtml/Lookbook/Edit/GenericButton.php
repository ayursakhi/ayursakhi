<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\Lookbook\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->context = $context;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Return Lookbook ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        try {
            if ($this->_coreRegistry->registry('lookbookpro_cdzlookbook')) {
                return $this->_coreRegistry->registry('lookbookpro_cdzlookbook')->getId();
            }
        } catch (NoSuchEntityException $e) {
        }
        return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}

