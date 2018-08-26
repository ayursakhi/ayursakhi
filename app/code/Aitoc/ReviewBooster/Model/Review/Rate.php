<?php
namespace Aitoc\ReviewBooster\Model\Review;

class Rate extends \Aitoc\ReviewBooster\Model\Review
{
    /**
     * Rate cookie name
     */
    const COOKIE_NAME_RATE = 'aitoc_review_booster_rate';

    /**
     * Rate types
     *
     * @var array
     */
    protected $rateTypes = [
        'helpful' => 1,
        'unhelpful' => 0
    ];

    /**
     * Save choice
     *
     * @param $review
     * @param $choice
     * @return bool
     */
    public function saveChoice($review, $choice)
    {
        if ($review->getId()) {
            $getCurrentValueName = 'getAitocReview' . ucfirst($choice);
            $setCurrentValueName = 'setAitocReview' . ucfirst($choice);
            $currentValue = $review->$getCurrentValueName();
            $newValue = $currentValue + 1;
            $review
                ->$setCurrentValueName($newValue)
                ->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Remember choice
     *
     * @param $reviewId
     * @param $choice
     */
    public function rememberChoice($reviewId, $choice)
    {
        $cookieMeta = $this->cookieMetadata->createPublicCookieMetadata()
            ->setDuration($this->getCookieDuration())
            ->setPath('/')
            ->setHttpOnly(false);
        $cookieName = $this->getCookieNameForSave($reviewId, $this->getCookieName());
        $this->cookie->setPublicCookie($cookieName, $this->rateTypes[$choice], $cookieMeta);
    }

    /**
     * Get review rate
     *
     * @param $reviewId
     * @return null
     */
    public function getReviewRate($reviewId)
    {
        if ($this->checkReviewStatus($reviewId)) {
            $rateCookie = $this->cookie->getCookie($this->getCookieName());
            $rateValue = $rateCookie[$reviewId];
            return $rateValue;
        } else {
            return null;
        }
    }

    /**
     * Get rate types
     *
     * @return array
     */
    public function getRateTypes()
    {
        return $this->rateTypes;
    }

    /**
     * Get rate cookie name
     *
     * @return string
     */
    public function getCookieName()
    {
        return self::COOKIE_NAME_RATE;
    }
}
