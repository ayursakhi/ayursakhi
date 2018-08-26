<?php
/**
 * Copyright © 2015 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Model\Plugin\ResourceModel;

use Magento\Framework\App\Filesystem\DirectoryList;

class Review
{
    /**
     * Configuration paths
     */
    const XML_PATH_ENABLE_NOTIFICATION = 'review_booster/notification_settings/enable_notification';
    const XML_PATH_EMAIL_RECIPIENT = 'review_booster/notification_settings/email_recipient';
    const REVIEW_IMG_DIR = 'review_booster';

    protected $request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Aitoc\ReviewBooster\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    protected $productRepository;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * Sender resolver
     *
     * @var \Magento\Framework\Mail\Template\SenderResolverInterface
     */
    protected $senderResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    protected $customerRepository;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Aitoc\ReviewBooster\Helper\Data $helper
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory $uploader
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResourceConnection $resource,
        \Aitoc\ReviewBooster\Helper\Data $helper,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository
    ) {
        $this->request = $request;
        $this->resource = $resource;
        $this->helper = $helper;
        $this->config = $config;
        $this->productRepository = $productRepository;
        $this->transportBuilder = $transportBuilder;
        $this->senderResolver = $senderResolver;
        $this->storeManager = $storeManager;
        $this->uploaderFactory = $uploaderFactory;
        $this->fileSystem = $fileSystem;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Save review extended data and send notification to admin
     *
     * @param \Magento\Review\Model\ResourceModel\Review $object
     * @param callable $proceed
     * @param AbstractModel $review
     * @return \Magento\Review\Model\ResourceModel\Review
     */
    public function aroundSave(
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $object,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $review
    ) {
        $detail = [];
        $isNew = false;
        if (!$review->getId()) {
            $isNew = true;
        }
        $result = $proceed($review);
        $adapter = $this->getWriteAdapter();
        $reviewId = $review->getReviewId();
        $customerId = $review->getCustomerId();
        $productId = $review->getEntityPkValue();
        if ($reviewId) {
            $condition = ['review_id = ?' => (int) $reviewId];
            $reviewData = $review->getData();
            if ($this->request->getModuleName() == 'aitocreviewbooster' &&
                $this->request->getControllerName() == 'review' &&
                $this->request->getActionName() == 'rateAjax'
            ) {
                if (isset($reviewData['aitoc_review_helpful'])) {
                    $detail['aitoc_review_helpful'] = $reviewData['aitoc_review_helpful'];
                }
                if (isset($reviewData['aitoc_review_unhelpful'])) {
                    $detail['aitoc_review_unhelpful'] = $reviewData['aitoc_review_unhelpful'];
                }
            }
            if ($this->request->getModuleName() == 'aitocreviewbooster' &&
                $this->request->getControllerName() == 'review' &&
                $this->request->getActionName() == 'reportAjax'
            ) {
                if (isset($reviewData['aitoc_review_reported'])) {
                    $detail['aitoc_review_reported'] = $reviewData['aitoc_review_reported'];
                }
            }
            if ($this->request->getModuleName() == 'review' &&
                $this->request->getControllerName() == 'product' &&
                ($this->request->getActionName() == 'post' || $this->request->getActionName() == 'save')
            ) {
                if (isset($reviewData['advantages'])) {
                    $detail['aitoc_product_advantages'] = $reviewData['advantages'];
                }
                if (isset($reviewData['disadvantages'])) {
                    $detail['aitoc_product_disadvantages'] = $reviewData['disadvantages'];
                }
                if ($this->isCustomerVerified($customerId, $productId)) {
                    $detail['aitoc_customer_verified'] = 1;
                }
                $reviewImage = $this->request->getFiles('image');
                if ($reviewImage['tmp_name']) {
                    $detail['image'] = $this->uploadFileAndGetName($reviewImage, $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getAbsolutePath(self::REVIEW_IMG_DIR . '/image'));
                }
                if (isset($reviewData['comment'])) {
                    $detail['comment'] = $reviewData['comment'];
                }

                if (isset($reviewData['admin_title'])) {
                    $detail['admin_title'] = $reviewData['admin_title'];
                }
            }
            if ($detail) {
                if (!$this->areExtendedDataSaved($reviewId)) {
                    $detail['review_id'] = $reviewId;
                    $adapter->insert($this->getTable('aitoc_review_booster_review_detail_extended'), $detail);
                } else {
                    $adapter->update(
                        $this->getTable('aitoc_review_booster_review_detail_extended'),
                        $detail,
                        $condition
                    );
                }
            }
            if ($isNew && $this->config->getValue(self::XML_PATH_ENABLE_NOTIFICATION)) {
                $this->sendReviewNotification($review);
            }
            if ($review->getComment() && $customerId && $review->getSendTo()) {
                $this->sendCommentNotification($review);
            }
        }
        return $result;
    }


    /**
     * Upload review image
     *
     * @param $input
     * @param $destinationFolder
     * @return string
     */
    public function uploadFileAndGetName($input, $destinationFolder)
    {
        $uploader = $this->uploaderFactory->create(['fileId' => $input]);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);
        $uploader->setAllowCreateFolders(true);
        $result = $uploader->save($destinationFolder);
        return $result['file'];
    }

    /**
     * Check has customer purchased reviewable product
     *
     * @param $customerId
     * @param $productId
     * @return bool
     */
    protected function isCustomerVerified($customerId, $productId)
    {
        if ($customerId && $productId) {
            return $this->helper->hasCustomerPurchasedProduct($customerId, $productId);
        } else {
            return false;
        }
    }

    /**
     * Check extended data existence for defined review
     *
     * @param $reviewId
     * @return bool
     */
    protected function areExtendedDataSaved($reviewId)
    {
        $adapter = $this->getReadAdapter();

        $extendedReviewData = $adapter->select()
            ->from(
                ['extended' => $this->getTable('aitoc_review_booster_review_detail_extended')],
                'extended_id'
            )
            ->where(
                'review_id = ?',
                $reviewId
            )
            ->query()
            ->fetch(\Zend_Db::FETCH_ASSOC);

        if ($extendedReviewData) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return validated table name
     *
     * @param string $table
     * @return string
     */
    protected function getTable($table)
    {
        return $this->resource->getTableName($table);
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function getWriteAdapter()
    {
        return $this->resource->getConnection('write');
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

    /**
     * Send review notification to admin
     *
     * @return void
     */
    public function sendReviewNotification($review)
    {
        if ($review) {
            $emailRecipient = $this->config->getValue(self::XML_PATH_EMAIL_RECIPIENT);
            $recipientData = $this->senderResolver->resolve($emailRecipient);
            $product = $this->productRepository->getById($review->getEntityPkValue());
            $productName = $product->getName();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(
                    'review_booster_admin_notification_template'
                )
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->storeManager->getStore()->getStoreId()
                    ]
                )
                ->setTemplateVars(
                    [
                        'product_name' => $productName
                    ]
                )
                ->addTo(
                    $recipientData['email'],
                    $recipientData['name']
                )
                ->getTransport();

            try {
                $transport->sendMessage();
            } catch (\Magento\Framework\Exception\MailException $e) {
                return false;
            }
        }
    }

    public function sendCommentNotification($review)
    {
        $customer = $this->customerRepository->getById($review->getCustomerId());
        $mail = $customer->getEmail();
        $product = $this->productRepository->getById($review->getEntityPkValue());
        $productName = $product->getName();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                'review_booster_comment_notification_template'
            )
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getStoreId()
                ]
            )
            ->setTemplateVars(
                [
                    'product_name' => $productName
                ]
            )
            ->addTo(
                [
                    'email' => $mail
                ]
            )
            ->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            return false;
        }
    }
}
