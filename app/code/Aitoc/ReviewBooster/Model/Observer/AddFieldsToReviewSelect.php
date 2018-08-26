<?php
namespace Aitoc\ReviewBooster\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddFieldsToReviewSelect implements ObserverInterface
{
    /**
     * Add custom fields to review select
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $collection = $event->getCollection();
        $select = $collection->getSelect();
        $select
            ->joinLeft(
                ['aitoc_extended' => $collection->getTable('aitoc_review_booster_review_detail_extended')],
                'aitoc_extended.review_id = main_table.review_id',
                [
                    'aitoc_product_advantages',
                    'aitoc_product_disadvantages',
                    'aitoc_review_helpful',
                    'aitoc_review_unhelpful',
                    'aitoc_customer_verified',
                    'aitoc_review_reported',
                    'image',
                    'comment',
                    'admin_title'
                ]
            );
    }
}
