<?php
namespace Aitoc\ReviewBooster\Model;

class Reminder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Configuration paths
     */
    const XML_PATH_EMAIL_SENDER = 'review_booster/general_settings/email_sender';

    const XML_PATH_EMAIL_TEMPLATE = 'review_booster/general_settings/template';

    const XML_PATH_IGNORED_CUSTOMER_GROUPS = 'review_booster/general_settings/ignore_group';

    const XML_PATH_SEND_EMAILS_AUTOMATICALLY = 'review_booster/general_settings/send_emails_automatically';

    const XML_PATH_DELAY_PERIOD = 'review_booster/general_settings/delay_period';

    const XML_PATH_GENERATE_DISCOUNTS = 'review_booster/discount_settings/generate_discounts';

    const XML_PATH_DISCOUNT_PERCENT = 'review_booster/discount_settings/discount_percent';

    const XML_PATH_DISCOUNT_PERIOD = 'review_booster/discount_settings/discount_period';

    /**
     * Review reminder statuses
     */
    const STATUS_PENDING = 'pending';

    const STATUS_SENT = 'sent';

    const STATUS_FAILED = 'failed';

    /**
     * @var ResourceModel\Reminder\CollectionFactory
     */
    protected $reminderCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollection;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollection;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    private $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     */
    private $couponMassgenerator;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $salesRule;

    /**
     * @var \Aitoc\ReviewBooster\Helper\Data
     */
    private $helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ResourceModel\Reminder\CollectionFactory $reminderCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\SalesRule\Model\RuleFactory $salesRule
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $couponMassgenerator
     * @param \Aitoc\ReviewBooster\Helper\Data $helper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aitoc\ReviewBooster\Model\ResourceModel\Reminder\CollectionFactory $reminderCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollection,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\SalesRule\Model\RuleFactory $salesRule,
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\Coupon\Massgenerator $couponMassgenerator,
        \Aitoc\ReviewBooster\Helper\Data $helper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reminderCollection = $reminderCollection;
        $this->orderCollection = $orderCollection;
        $this->customerCollection = $customerCollection;
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->dateFactory = $dateFactory;
        $this->ruleFactory = $ruleFactory;
        $this->couponFactory = $couponFactory;
        $this->couponMassgenerator = $couponMassgenerator;
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->transportBuilder = $transportBuilder;
        $this->salesRule = $salesRule;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Aitoc\ReviewBooster\Model\ResourceModel\Reminder');
    }

    /**
     * Add reminders for unprocessed orders
     *
     * @return $this
     */
    public function addReminders()
    {
        $orders = $this->getUnprocessedOrders();

        foreach ($orders->getItems() as $order) {
            $salesRuleId = null;
            if ($customerId = $order->getCustomerId()) {
                $customer = $this->customerFactory->create()->load($order->getCustomerId());
                $salesRuleId = $this->getSalesRuleId($customer);
            }
            $data = [
                'store_id' => $order->getStoreId(),
                'customer_id' => $order->getCustomerId(),
                'order_id' => $order->getId(),
                'customer_name' => $order->getCustomerFirstname(),
                'customer_email' => $order->getCustomerEmail(),
                'products' => serialize($this->getOrderProducts($order)),
                'sales_rule_id' => $salesRuleId,
                'unsubscribe_code' => md5(uniqid())
            ];
            $this->addData($data)->save();
            $this->unsetData();
        }

        return $this;
    }

    /**
     * Get unprocessed orders
     *
     * @return mixed
     */
    public function getUnprocessedOrders()
    {
        $orders = $this->orderCollection->create();
        if ($this->getAllowedStatuses()) {
            $orders->addFieldToFilter('main_table.status', ['in' => $this->getAllowedStatuses()]);
        }
        if ($this->getProcessedOrders()) {
            $orders->addFieldToFilter('main_table.entity_id', ['nin' => $this->getProcessedOrders()]);
        }

        if ($ignoredCustomerGroupIds = $this->config->getValue(self::XML_PATH_IGNORED_CUSTOMER_GROUPS)) {
            $orders->addFieldToFilter('main_table.customer_group_id', ['nin' => $ignoredCustomerGroupIds]);
        }

        $orders = $this->filterOrdersByCustomers($orders);

        return $orders->load();
    }

    /**
     * Filter orders by unsubscribed customers
     *
     * @returm mixed
     */
    public function filterOrdersByCustomers($ordersCollection)
    {
        $ignoredCustomerIds = [];
        $customers = $this->customerCollection->create();
        $customers->addAttributeToFilter('is_review_booster_subscriber', 0);
        foreach ($customers as $customer) {
            $ignoredCustomerIds[] = $customer->getId();
        }
        if ($ignoredCustomerIds) {
            $ordersCollection->addFieldToFilter('main_table.customer_id', ['nin' => $ignoredCustomerIds]);
        }
        return $ordersCollection;
    }

    /**
     * Get list of processed orders
     *
     * @return array
     */
    public function getProcessedOrders()
    {
        $readAdapter = $this->getResource()->getConnection();
        $processedOrderIds = $readAdapter->select()
            ->from(
                ['reminder' => $this->getResource()->getTable('aitoc_review_booster_reminder')],
                ['order_id']
            )
            ->query()
            ->fetchAll(\Zend_Db::FETCH_COLUMN);

        return $processedOrderIds;
    }

    /**
     * Get list of allowed statuses to send reminders
     *
     * @return array
     */
    public function getAllowedStatuses()
    {
        $statuses = [
            'complete'
        ];

        return $statuses;
    }

    /**
     * Get list of order products
     *
     * @param $order
     * @return array
     */
    public function getOrderProducts($order)
    {
        $products = [];
        $items = $order->getAllItems();
        foreach ($items as $item) {
            $productItem = $item->getProduct();
            if ($productItem && !$item->getParentItemId()) {
                $product['id'] = $item->getId();
                $product['name'] = $item->getName();
                $product['url'] = $productItem->getProductUrl();
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Get sales rule id
     *
     * @param $customer
     * @param $campaign
     *
     * @return mixed
     */
    public function getSalesRuleId($customer)
    {
        return $this->generateIndividualRule($customer)->getId();
    }

    /**
     * Generate individual sales rule
     *
     * @param $customer
     *
     * @return \Magento\Framework\Object
     */
    public function generateIndividualRule($customer)
    {
        if ($customer->getId()) {
            $ruleData = [
                'name'                => __('Individual sales rule for %1', $customer->getEmail()),
                'is_active'           => 1,
                'simple_action'       => 'by_percent',
                'discount_amount'     => $this->config->getValue(self::XML_PATH_DISCOUNT_PERCENT),
                'coupon_type'         => 2,
                'website_ids'         => [$customer->getWebsiteId()],
                'customer_group_ids'  => $this->helper->getCustomerGroups(),
                'uses_per_coupon'     => 1
            ];

            $rule = $this->ruleFactory->create()
                ->addData($ruleData)
                ->save();

            $code = $this->couponMassgenerator
                ->setLength(12)
                ->generateCode();
            $couponData = [
                'rule_id'            => $rule->getId(),
                'code'               => $code,
                'usage_limit'        => 1,
                'usage_per_customer' => 1,
                'is_primary'         => 1
            ];

            $this->couponFactory->create()
                ->addData($couponData)
                ->save();
        } else {
            $rule = new \Magento\Framework\DataObject();
        }

        return $rule;
    }

    /**
     * Send pending reminders
     *
     * @return $this
     */
    public function sendReminders()
    {
        if (!$this->config->getValue(self::XML_PATH_SEND_EMAILS_AUTOMATICALLY)) {
            return $this;
        }

        $reminderData = $this->getData();
        $remindSenderContact = $this->config->getValue(self::XML_PATH_EMAIL_SENDER);
        $products = unserialize($reminderData['products']);
        $orderProducts = $this->_getProductsHtml($products);
        $emailTemplate = $this->config->getValue(self::XML_PATH_EMAIL_TEMPLATE);
        $coupon = new \Magento\Framework\DataObject();


        if ($reminderData['sales_rule_id']) {
            $salesRule = $this->salesRule->create()
                ->load($reminderData['sales_rule_id']);

            if ($salesRule->getPrimaryCoupon()->getCode()) {
                $discountAmount = $this->config->getValue(self::XML_PATH_DISCOUNT_PERCENT);
                $discountPeriod = $this->config->getValue(self::XML_PATH_DISCOUNT_PERIOD);
                $coupon
                    ->addData([
                        'coupon_code' => $salesRule->getPrimaryCoupon()->getCode(),
                        'discount_amount' => (int)$discountAmount . '%',
                        'expiry_days' => $discountPeriod
                    ]);
            }
        }

        $transport = $this->transportBuilder
            ->setTemplateIdentifier(
                $emailTemplate
            )
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getStoreId()
                ]
            )
            ->setFrom(
                $remindSenderContact
            )
            ->setTemplateVars(
                [
                    'customer_name' => $this->getCustomerName(),
                    'order_products' => $orderProducts,
                    'coupon' => $coupon,
                    'alert_code' => $this->getUnsubscribeCode(),
                    'alert_id' => $this->getId()
                ]
            )
            ->addTo(
                $this->getCustomerEmail(),
                $this->getCustomerName()
            )
            ->getTransport();

        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            $this->setStatus(self::STATUS_FAILED)
                ->save();
            $this->_logger->critical($e);
            return $this;
        }

        $timestamp = $this->dateFactory->create()->gmtDate();
        $this->setStatus(self::STATUS_SENT)
            ->setSentAt($timestamp)
            ->save();

        return $this;
    }

    /**
     * Get html links of products
     *
     * @param array $products
     * @return string
     */
    protected function _getProductsHtml($products)
    {
        $orderProducts = '';
        if (is_array($products)) {
            foreach ($products as $product) {
                $orderProducts .= '<a href="' . htmlspecialchars($product['url']) . '">' .
                    htmlspecialchars($product['name']) . '</a><br/>';
            }
        }

        return $orderProducts;
    }
}
