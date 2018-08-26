<?php
namespace Aitoc\ReviewBooster\Helper;

class Report extends \Aitoc\ReviewBooster\Helper\Data
{
    /**
     * Report review message
     */
    const MESSAGE_REPORTED = 'You reported this review.';

    /**
     * Check is review reported
     *
     * @param $reviewId
     * @return int
     */
    public function isReviewReported($reviewId)
    {
        $reported = $this->reportModel->checkReviewStatus($reviewId);

        return $reported;
    }

    /**
     * Get report URL
     *
     * @return string
     */
    public function getReportUrl()
    {
        return $this->_getUrl('aitocreviewbooster/review/reportAjax');
    }

    /**
     * Get reported message
     *
     * @return string
     */
    public function getReportedMessage()
    {
        return self::MESSAGE_REPORTED;
    }
}
