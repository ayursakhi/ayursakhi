<?php
namespace Aitoc\ReviewBooster\Model\Review;

class Report extends \Aitoc\ReviewBooster\Model\Review
{
    /**
     * Report cookie name
     */
    const COOKIE_NAME_REPORT = 'aitoc_review_booster_report';

    /**
     * Save report
     *
     * @param $review
     * @param $report
     * @return bool
     */
    public function saveReport($review, $report)
    {
        if ($review->getId()) {
            $currentValue = $review->getAitocReviewReported();
            $newValue = $currentValue + 1;
            $review
                ->setAitocReviewReported($newValue)
                ->save();

            return true;
        } else {
             return false;
        }
    }

    /**
     * Remember report
     *
     * @param $reviewId
     * @param $report
     */
    public function rememberReport($reviewId, $report)
    {
        $cookieMeta = $this->cookieMetadata->createPublicCookieMetadata()
            ->setDuration($this->getCookieDuration())
            ->setPath('/')
            ->setHttpOnly(false);
        $cookieName = $this->getCookieNameForSave($reviewId, $this->getCookieName());
        $this->cookie->setPublicCookie($cookieName, 1, $cookieMeta);
    }

    /**
     * Get report cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        return self::COOKIE_NAME_REPORT;
    }
}
