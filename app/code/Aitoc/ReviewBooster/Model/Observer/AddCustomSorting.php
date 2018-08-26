<?php
namespace Aitoc\ReviewBooster\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddCustomSorting implements ObserverInterface
{
    /**
     * Add custom sorting to raise more helpful reviews
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $select = $event->getCollection()->getSelect();
        $select->order('ABS(IF(aitoc_review_helpful IS NULL, 0, aitoc_review_helpful) -' .
        'IF(aitoc_review_unhelpful IS NULL, 0, aitoc_review_unhelpful)) DESC');
    }
}
