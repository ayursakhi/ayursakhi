<?php
/**
 * Copyright © 2016 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\Order as OrderModel;

/**
 * Class Order
 */
class Order extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $orderModel;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderModel $orderModel,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderModel = $orderModel;
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
                $item[$this->getData('name')] = $this->prepareItem($item);
            }
        }

        return $dataSource;
    }

    /**
     * Get data
     *
     * @param array $item
     * @return string
     */
    protected function prepareItem(array $item)
    {
        $errorMessage = __('Invalid Data');
        $content = '';
        $orderId = $item['order_id'];
        if (isset($orderId)) {
            $order = $this->orderModel->load($orderId);
            if ($order->getId()) {
                $orderUrl = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => $orderId]);
                $content = "<a href='" . $orderUrl . "'>View</a>";
            } else {
                return $errorMessage;
            }
        } else {
            return $errorMessage;
        }
        return $content;
    }
}
