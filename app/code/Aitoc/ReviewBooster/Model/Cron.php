<?php
namespace Aitoc\ReviewBooster\Model;

class Cron
{
    /**
     * @var \Aitoc\ReviewBooster\Model\ReminderFactory
     */
    protected $reminder;

    /**
     * @var \Aitoc\ReviewBooster\Model\ResourceModel\Reminder\CollectionFactory
     */
    protected $reminderCollection;

    /**
     * Class constructor
     *
     * @param ReminderFactory $reminderFactory
     * @param ResourceModel\Reminder\CollectionFactory $reminderCollectionFactory
     */
    public function __construct(
        \Aitoc\ReviewBooster\Model\ReminderFactory $reminderFactory,
        \Aitoc\ReviewBooster\Model\ResourceModel\Reminder\CollectionFactory $reminderCollectionFactory
    ) {
        $this->reminder = $reminderFactory;
        $this->reminderCollection = $reminderCollectionFactory;
    }

    /**
     * Generate reminders
     *
     * @return $this
     */
    public function generateReminders()
    {
        $this->reminder->create()
            ->addReminders();

        return $this;
    }

    /**
     * Process generated reminders
     *
     * @return $this
     */
    public function processReminders()
    {
        $collection = $this->reminderCollection->create()
            ->setPageSize(20)
            ->setCurPage(1)
            ->addFilterByStatus('pending')
            ->setSendOrder()
            ->setDelayPeriod()
            ->load();
        $collection->walk('sendReminders', [20]);

        return $this;
    }
}
