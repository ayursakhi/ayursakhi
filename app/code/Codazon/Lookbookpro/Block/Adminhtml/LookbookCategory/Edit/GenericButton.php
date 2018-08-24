<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class GenericButton
 */
class GenericButton extends \Magento\Backend\Block\Template
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Registry
     */
    protected $_coreRegistry;
    
    protected $_storeManager;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry
    ) {
        $this->context = $context;
        $this->_storeManager = $context->getStoreManager();
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Return Lookbook Category ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        try {
            if ($this->_coreRegistry->registry('lookbookpro_cdzlookbook_category')) {
                return $this->_coreRegistry->registry('lookbookpro_cdzlookbook_category')->getId();
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
    
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [\Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID];
            foreach ($this->_storeManager->getStores() as $store) {
                $ids[] = $store->getConfig('codazon_lookbook/general/root_category');
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
    
}

