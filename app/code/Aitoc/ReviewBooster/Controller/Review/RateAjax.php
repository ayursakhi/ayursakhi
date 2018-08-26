<?php
namespace Aitoc\ReviewBooster\Controller\Review;

use Aitoc\ReviewBooster\Controller\Review;

class RateAjax extends Review
{
    /**
     * Send review rate choice
     *
     * @return void
     */
    public function execute()
    {
        $reviewId = $this->_request->getParam('reviewId');
        $choice = $this->_request->getParam('choice');
        $review = $this->reviewModel->loadReview($reviewId);
        if ($this->rateModel->saveChoice($review, $choice)) {
            $this->rateModel->rememberChoice($reviewId, $choice);
        }
    }
}
