<?php
namespace Aitoc\ReviewBooster\Model\ResourceModel\Reminder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $config;

    /**
     * Main table primary key field name
     *
     * @var string
     */
    protected $_idFieldName = 'reminder_id';

    /**
     * Class constructor
     *
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Backend\App\ConfigInterface $config,
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->config = $config;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define resource model and model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Aitoc\ReviewBooster\Model\Reminder', 'Aitoc\ReviewBooster\Model\ResourceModel\Reminder');
        parent::_construct();
    }

    /**
     * Add status filter
     *
     * @param $status
     * @return $this
     */
    public function addFilterByStatus($status)
    {
        $this->addFieldToFilter('status', $status);

        return $this;
    }

    /**
     * Set order to send oldest reminders first
     *
     * @return $this
     */
    public function setSendOrder()
    {
        $this->addOrder('created_at', self::SORT_ORDER_DESC);

        return $this;
    }


    /**
     * Set delay period for reminders
     *
     * @return $this
     */
    public function setDelayPeriod()
    {
        $period = $this->config->getValue(\Aitoc\ReviewBooster\Model\Reminder::XML_PATH_DELAY_PERIOD);
        $this->getSelect()
            ->where(
                'created_at < NOW() - INTERVAL ? DAY',
                $period
            );

        return $this;
    }
}
