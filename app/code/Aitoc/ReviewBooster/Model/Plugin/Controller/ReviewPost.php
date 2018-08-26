<?php
namespace Aitoc\ReviewBooster\Model\Plugin\Controller;

class ReviewPost
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
     * Change nickname to 'anonymous' if is empty
     *
     * @param \Magento\Review\Model\Review $review
     * @return \Magento\Review\Model\Review
     */
    public function beforeExecute()
    {
        $requestParams = $this->request->getPostValue();
        if (!$requestParams['nickname']) {
            $requestParams['nickname'] = 'anonymous';
            $this->request->setPostValue($requestParams);
        }
    }
}
