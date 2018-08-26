<?php
/**
 * Copyright © 2017 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Controller\Review;

use \Magento\Framework\App\Action\Action;

class Unsubscribe extends Action
{
    /**
     * @var \Aitoc\ReviewBooster\Model\ReminderFactory
     */
    private $reminderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Aitoc\ReviewBooster\Model\ReminderFactory,
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Aitoc\ReviewBooster\Model\ReminderFactory $reminderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->reminderFactory = $reminderFactory;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }
    /**
     * Unsubscribe from review riminders
     *
     * @return void
     */
    public function execute()
    {
        $reminderId = $this->getRequest()->getParam('id');
        $code = $this->getRequest()->getParam('code');
        $reminder = $this->reminderFactory->create()
            ->load($reminderId);
        if ($reminder->getUnsubscribeCode() == $code) {
            try {
                $customerId = $reminder->getCustomerId();
                if ($customerId) {
                    $customer = $this->customerFactory->create()->load($customerId);
                    $customer->setData('is_review_booster_subscriber', 0)->getResource()->saveAttribute($customer, 'is_review_booster_subscriber');
                }

                $this->messageManager->addSuccess(__('You have been successfully unsubscribed.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addException($e, $e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while unsubscribing you.'));
            }
        }

        return $this->_redirect('customer/account');
    }
}
