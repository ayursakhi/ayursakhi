<?php
namespace Aitoc\ReviewBooster\Controller;

use Magento\Review\Model\Review as ProductReview;
use Aitoc\ReviewBooster\Model\Review as ReviewModel;

abstract class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Review\Model\Review
     */
    protected $review;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review
     */
    protected $reviewModel;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review\Report
     */
    protected $reportModel;

    /**
     * @var \Aitoc\ReviewBooster\Model\Review\Rate
     */
    protected $rateModel;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param ProductReview $review
     * @param ReviewModel $reviewModel
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param ReviewModel\Report $reportModel
     * @param ReviewModel\Rate $rateModel
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ProductReview $review,
        ReviewModel $reviewModel,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Aitoc\ReviewBooster\Model\Review\Report $reportModel,
        \Aitoc\ReviewBooster\Model\Review\Rate $rateModel
    ) {
        $this->review = $review;
        $this->reviewModel = $reviewModel;
        $this->reviewFactory = $reviewFactory;
        $this->reportModel = $reportModel;
        $this->rateModel = $rateModel;

        parent::__construct($context);
    }
}
