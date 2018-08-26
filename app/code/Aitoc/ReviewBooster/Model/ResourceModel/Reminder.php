<?php
namespace Aitoc\ReviewBooster\Model\ResourceModel;

class Reminder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('aitoc_review_booster_reminder', 'reminder_id');
    }
}
