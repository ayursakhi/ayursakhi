<?php
/**
 *
 * Copyright © 2016 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Controller\Adminhtml\Alerts;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aitoc\ReviewBooster\Model\ResourceModel\Reminder\CollectionFactory;

class MassStatus extends \Magento\Backend\App\Action
{
    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $alertsChanged = 0;
        $status = $this->getRequest()->getParam('status');
        foreach ($collection->getItems() as $alert) {
            $alert->setStatus($status);
            $alertsChanged++;
        }
        $collection->save();
        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been changed.', $alertsChanged)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('reviewbooster/*/index');
    }
}
