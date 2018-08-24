<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Codazon\Shopbybrandpro\Controller\Index;

use Magento\Framework\View\Result\LayoutFactory;

class SearchBrands extends \Magento\Framework\App\Action\Action
{
    
    protected $_brandObject;
    
    protected $_context;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LayoutFactory $resultLayoutFactory,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory
    ) {
        parent::__construct($context);
        $this->_urlManager = $context->getUrl();
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_brandFactory = $brandFactory;
        $this->_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->_imageHelper = $this->_objectManager->get('Codazon\Shopbybrandpro\Helper\Image');
        $this->_attributeCode = $this->_objectManager
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
			->getValue('codazon_shopbybrand/general/attribute_code');
    }
    
    public function getUrl($urlKey, $params = null)
    {
        return $this->_urlManager->getUrl($urlKey, $params);
    }
    
    public function getAllBrandsArray($query = false, $orderBy = 'brand_label', $order = 'asc')
    {
        if (!$this->_brandObject) {
            $this->_brandObject = [];
			$brand = $this->_brandFactory->create();		
			$col = $brand->getCollection();
			$connection = $col->getConnection();
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $storeId = $this->_storeManager->getStore()->getId();
            
			$select = $connection->select();
			$select->from(['main_table' => $col->getTable('eav_attribute_option') ], ['option_id', 'attribute_id', 'sort_order'])
				->joinLeft([ 'cea' => $col->getTable('catalog_eav_attribute') ],'main_table.attribute_id = cea.attribute_id')
				->joinLeft([ 'ea' => $col->getTable('eav_attribute') ],'cea.attribute_id = ea.attribute_id', ['attribute_code'])
				->joinLeft([ 'eaov' => $col->getTable('eav_attribute_option_value') ], 'eaov.option_id = main_table.option_id', ['brand_label' => 'value', 'store_id'])
				->where("ea.attribute_code = '{$this->_attributeCode}'")
                ->where("eaov.store_id IN ({$defaultStoreId}, {$storeId})")
				->group("main_table.option_id")
				->order($orderBy.' '.$order);
                
            if ($query) {
                $select->where("value LIKE '%{$query}%'");
            }
            
			$rows = $connection->fetchAll($select);
            
            if (count($rows) > 0) {
                $optionIds = [];
                foreach ($rows as $row) {
                    $optionIds[] = $row['option_id'];
                }
                $brandItems = $this->_brandFactory
                    ->create()
                    ->getCollection()->setStore($storeId)
                    ->addFieldToFilter('option_id', ['in' => $optionIds])
                    ->addAttributeToSelect(['brand_thumbnail','brand_url_key'])->getItems();
                $brands = [];
                foreach ($brandItems as $brandItem) {
                    $brands[$brandItem->getData('option_id')] = $brandItem;
                }
				foreach ($rows as $row) {
                    $optionId = $row['option_id'];
                    if (isset($brands[$optionId])) {
                        if (isset($brands[$optionId]['is_active'])) {
                            if ($brands[$optionId]['is_active'] == 0) {
                                continue;
                            }
                        }
                        $brandModel = $brands[$optionId]->addData($row);
                    } else {                    
                        $brandModel = new \Magento\Framework\DataObject($row);
					}
                    if ($brandModel->getData('brand_url_key')) {
                        $brandModel->setUrl($this->getUrl('brand') . $brandModel->getData('brand_url_key'));
                    } else {
                        $brandModel->setUrl($this->getUrl('brand').urlencode(str_replace(' ','-',strtolower(trim($brandModel->getData('brand_label'))))));
                    }
                    $this->_brandObject[] = $brandModel;
				}
			}
		}
		return $this->_brandObject;
    }
    
    public function execute()
    {
        $brandLabels = [];
        $query = $this->getRequest()->getParam('term', false);
        $brandData = $this->getAllBrandsArray($query);
        if (count($brandData)) {
            foreach ($brandData as $brand) {
                $brandLabels[] = [
                    'label' => $brand->getData('brand_label'),
                    'value' => $brand->getData('brand_label'),
                    'url'   => $brand->getData('url'),
                    'img'   => $this->getThumbnailImage($brand, ['width' => 50, 'height' => 50])
                ];
            }
        }
        echo json_encode($brandLabels); die();
    }
    
    public function getThumbnailImage($brand, array $options = []) {
		if (!($brandThumb = $brand->getBrandThumbnail())) {
			$brandThumb = 'codazon/brand/placeholder_thumbnail.jpg';
        }
        if (isset($options['width']) || isset($options['height'])) {
            if(!isset($options['width']))
                $options['width'] = null;
            if(!isset($options['height']))
                $options['height'] = null;
            return $this->_imageHelper->init($brandThumb)->resize($options['width'],$options['height'])->__toString();
        } else {
            return $this->_mediaUrl.$brandThumb;
        }
	}
    
}