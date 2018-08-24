<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Codazon\Shopbybrandpro\Helper\Data as ShopbybrandproHelper;

class ShopbybrandproPreview extends Column
{

    protected $helper;
	/**
	* @param ContextInterface $context
	* @param UiComponentFactory $uiComponentFactory
	* @param UrlInterface $urlBuilder
	* @param array $components
	* @param array $data
	* @param ShopbybrandproHelper $helper
	*/
	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		array $components = [],
		array $data = [],
		ShopbybrandproHelper $helper
	) {
        $this->helper = $helper;
		parent::__construct($context, $uiComponentFactory, $components, $data);
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
			foreach ($dataSource['data']['items'] as & $item) {
				$name = $this->getData('name');
				if (isset($item['option_id'])) {
                    if (isset($item['brand_object'])) {
                        $brand = $item['brand_object'];
                    } else {
                        $model = $objectManager->create('Codazon\Shopbybrandpro\Model\Brand');
                        $model->setOptionId($item['option_id']);
                        $brand = $model->load(null);
                        $item['brand_object'] = $brand;
                    }
					$item[$name]['edit'] = [
						'href'      => $this->helper->getBrandPageUrl($brand),
						'label'     => __('Preview'),
                        'target'    => 'blank'
					];
				}
			}
		}
		return $dataSource;
	}
}