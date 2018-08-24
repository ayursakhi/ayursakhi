<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\LookbookItem;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;

/**
 * Class Builder
 */
class Builder extends \Magento\Backend\Block\Template
{
    protected $_assetRepo;
    protected $_registry;
    protected $_objectManager;
    
    public function __construct(
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
		array $data = [])
    {
		$this->_assetRepo = $context->getAssetRepository();
        $this->_registry = $registry;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context, $data);
    }
    
    public function getMediaUrl($path = '')
    {
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).$path;
	}
    
    public function getConfig() {
        $jsonArray = [
            'mediaUrl'   => $this->getMediaUrl(),
            'productUrl' => $this->getUrl('catalog/product_widget/chooser')
        ];
        $jsonArray['products'] = [];
        if ($item = $this->_registry->registry('lookbookpro_cdzlookbook_item')) {
            if ($data = $item->getData('item_data')) {
                $data = json_decode($data, true);
                if (isset($data['points'])) {
                    foreach ($data['points'] as $point) {
                        if (isset($point['productId']) && !isset($jsonArray['products'][$point['productId']])) {
                            $productId = (int)$point['productId'];
                            $product = $this->_objectManager->create('Magento\Catalog\Model\Product')
                                ->getCollection()->addAttributeToSelect('name')
                                ->addFieldToFilter('entity_id', $productId)
                                ->getFirstItem();
                            
                            $jsonArray['products'][$productId] = [
                                'id'    => $product->getId(),
                                'name'  => $product->getName()
                            ];
                        }
                    }
                }
            }
        }
        return $jsonArray;
    }
}
