<?php
namespace Aitoc\ReviewBooster\Helper;

class Rate extends \Aitoc\ReviewBooster\Helper\Data
{
    /**
     * Helpful review message
     */
    const MESSAGE_HELPFUL = 'You have found this helpful.';

    /**
     * Unhelpful review message
     */
    const MESSAGE_UNHELPFUL = 'You have found this unhelpful.';

    /**
     * Get rate URL
     *
     * @return string
     */
    public function getRateUrl()
    {
        return $this->_getUrl('aitocreviewbooster/review/rateAjax');
    }

    /**
     * Check is review rated
     *
     * @param $reviewId
     * @return bool
     */
    public function isReviewRated($reviewId)
    {
        $rateModel = $this->rateModel;
        $isRated = $rateModel->checkReviewStatus($reviewId);

        return $isRated;
    }

    /**
     * Get rate choice message
     *
     * @param $reviewId
     * @return string
     */
    public function getChoiceMessage($reviewId)
    {
        $rateModel = $this->rateModel;
        $rate = $rateModel->getReviewRate($reviewId);
        $rateTypes = $rateModel->getRateTypes();
        if ($rate == $rateTypes['helpful']) {
            return $this->getHelpfulMessage();
        } elseif ($rate == $rateTypes['unhelpful']) {
            return $this->getUnhelpfulMessage();
        }
    }

    /**
     * Get helpful message
     *
     * @return string
     */
    public function getHelpfulMessage()
    {
        return self::MESSAGE_HELPFUL;
    }

    /**
     * Get unhelpful message
     *
     * @return string
     */
    public function getUnhelpfulMessage()
    {
        return self::MESSAGE_UNHELPFUL;
    }
}
