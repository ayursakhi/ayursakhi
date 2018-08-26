<?php
namespace Aitoc\ReviewBooster\Model\Observer;

use Magento\Framework\Event\ObserverInterface;

class AddRatingFilter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Aitoc\ReviewBooster\Helper\Data
     */
    protected $helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Aitoc\ReviewBooster\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Aitoc\ReviewBooster\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Add rating filter
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $rating = $this->request->getParam('rating');
        $ratingInterval = $this->helper->getRatingIntervals();
        $collection = $event->getCollection();
        $select = $collection->getSelect();
        $select
            ->joinLeft(
                ['rating_vote' => $collection->getTable('rating_option_vote')],
                'rating_vote.review_id = main_table.review_id',
                [
                    'group_concat(percent) as percent',
                    'count(*) as vote_count'
                ]
            );
        $select->group('main_table.review_id');
        if ($rating) {
            $select
                ->where(
                    'rating_vote.percent <= ' . $ratingInterval[$rating]['max']
                )
                ->where(
                    'rating_vote.percent >= ' . $ratingInterval[$rating]['min']
                );
        }
    }
}
