<?php
namespace Aitoc\ReviewBooster\Model\Plugin;

class Review
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->request = $request;
        $this->resource = $resource;
    }

    /**
     * Add extended data to a loaded review
     *
     * @param \Magento\Review\Model\Review $review
     * @return \Magento\Review\Model\Review
     */
    public function afterLoad(\Magento\Review\Model\Review $review)
    {
        $adapter = $this->getReadAdapter();
        $reviewOriginalId = $review->getId();
        $reviewRequestId = $this->request->getParam('reviewId');
        if ($reviewOriginalId) {
            $reviewId = $reviewOriginalId;
        } elseif ($reviewRequestId) {
            $reviewId = $reviewRequestId;
        }
        if ($reviewId) {
            $extendedData = $adapter->select()
                ->from($this->getTable('aitoc_review_booster_review_detail_extended'))
                ->where('review_id = ?', $reviewId)
                ->limit(1)
                ->query()
                ->fetch(\Zend_Db::FETCH_ASSOC);
            if (is_array($extendedData)) {
                $review->addData($extendedData);
            }
        }

        return $review;
    }

    /**
     * Return validated table name
     *
     * @param string|string[] $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getReadAdapter()
    {
        return $this->resource->getConnection('read');
    }
}
