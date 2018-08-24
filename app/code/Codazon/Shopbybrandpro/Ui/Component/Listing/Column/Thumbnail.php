<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Shopbybrandpro\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'thumbnail';
    const ALT_FIELD = 'name';
	const BRAND_URL_PATH_EDIT = 'shopbybrandpro/index/edit';
	const BRAND_URL_PATH_DELETE = 'shopbybrandpro/index/delete';
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
		$this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_imageHelper = $this->_objectManager->get('Codazon\Shopbybrandpro\Helper\Image');
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {	
   	    if (isset($dataSource['data']['items'])) {
			$objectManager = $this->_objectManager;
			$mediaUrl = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			$repository = $objectManager->get('Magento\Framework\View\Asset\Repository');
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
				if (isset($item['brand_object'])) {
                    $brand = $item['brand_object'];
                } else {
                    $model = $objectManager->create('Codazon\Shopbybrandpro\Model\Brand');
                    $model->setOptionId($item['option_id']);
                    $brand = $model->load(null);
                    $item['brand_object'] = $brand;
                }
				if($brand->getBrandThumbnail()){
					$brandThumbnail = $this->_imageHelper->init($brand->getBrandThumbnail())->resize(100)->__toString();
					$brandOriginal = $mediaUrl.$brand->getBrandThumbnail();
				}else{
					$brandThumbnail = $brandOriginal = $repository->getUrl('Codazon_Shopbybrandpro/images/placeholder_thumbnail.jpg');
				}
                $item[$fieldName . '_src'] = $brandThumbnail;
                $item[$fieldName . '_alt'] = $brand->getBrandLabel();
                $item[$fieldName . '_link'] = $this->_urlBuilder->getUrl(self::BRAND_URL_PATH_EDIT, ['option_id' => $item['option_id']]);
                $item[$fieldName . '_orig_src'] = $brandOriginal;
            }
        }
        return $dataSource;
    }
}
