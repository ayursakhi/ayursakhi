<?php
namespace Aitoc\ReviewBooster\Block\Rewrite\Review\Adminhtml\Edit;

class Form extends \Magento\Review\Block\Adminhtml\Edit\Form
{
    /**
     * Prepare edit review form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $review = $this->_coreRegistry->registry('review_data');
        $product = $this->_productFactory->create()->load($review->getEntityPkValue());

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl(
                        'review/*/save',
                        [
                            'id' => $this->getRequest()->getParam('id'),
                            'ret' => $this->_coreRegistry->registry('ret')
                        ]
                    ),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'review_details',
            ['legend' => __('Review Details'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'product_name',
            'note',
            [
                'label' => __('Product'),
                'text' => '<a href="' . $this->getUrl(
                    'catalog/product/edit',
                    ['id' => $product->getId()]
                ) . '" onclick="this.target=\'blank\'">' . $this->escapeHtml(
                    $product->getName()
                ) . '</a>'
            ]
        );

        try {
            $customer = $this->customerRepository->getById($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escapeHtml($customer->getFirstname()),
                $this->escapeHtml($customer->getLastname()),
                $this->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($review->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }

        if ($review->getAitocCustomerVerified() == 1) {
            $verifiedCustomer = __(
                ' - <span title="Verified reviews are written by shoppers who purchased this item.">verified</span>'
            );
        } else {
            $verifiedCustomer = __(
                ' - <span title="Review posted by an unregistered customer or by a customer who never purchased' .
                ' this item.">not verified</span>'
            );
        }

        $fieldset->addField('customer', 'note', ['label' => __('Author'), 'text' => $customerText . $verifiedCustomer]);

        $fieldset->addField(
            'summary-rating',
            'note',
            [
                'label' => __('Summary Rating'),
                'text' => $this->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Rating\Summary')->toHtml()
            ]
        );

        $fieldset->addField(
            'detailed-rating',
            'note',
            [
                'label' => __('Detailed Rating'),
                'required' => true,
                'text' => '<div id="rating_detail">' . $this->getLayout()->createBlock(
                    'Magento\Review\Block\Adminhtml\Rating\Detailed'
                )->toHtml() . '</div>'
            ]
        );

        $fieldset->addField(
            'status_id',
            'select',
            [
                'label' => __('Status'),
                'required' => true,
                'name' => 'status_id',
                'values' => $this->_reviewData->getReviewStatusesOptionArray()
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->hasSingleStore()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'required' => true,
                    'name' => 'stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
            $review->setSelectStores($review->getStores());
        } else {
            $fieldset->addField(
                'select_stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $review->setSelectStores($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'nickname',
            'text',
            ['label' => __('Nickname'), 'required' => true, 'name' => 'nickname']
        );

        $fieldset->addField(
            'title',
            'text',
            ['label' => __('Summary of Review'), 'required' => true, 'name' => 'title']
        );

        $fieldset->addField(
            'detail',
            'textarea',
            ['label' => __('Review'), 'required' => true, 'name' => 'detail', 'style' => 'height:24em;']
        );

        $fieldset->addField(
            'aitoc_product_advantages',
            'text',
            ['label' => __('What I like about this product'), 'required' => false, 'name' => 'advantages']
        );

        $fieldset->addField(
            'aitoc_product_disadvantages',
            'text',
            ['label' => __('What I dislike about this product'), 'required' => false, 'name' => 'disadvantages']
        );

        $fieldset->addField(
            'aitoc_review_image',
            'note',
            ['label' => 'Image', 'text' => __('<img class="aitoc_review_image" src="%1"/>', $this->_storeManager->getStore()->getBaseUrl() . 'pub/media/review_booster/image' . $review->getImage())]
        );

        $fieldset->addField(
            'aitoc_review_reported',
            'note',
            ['label' => '', 'text' => __('%1 abuse reports submitted', $review->getAitocReviewReported())]
        );

        $fieldset->addField(
            'aitoc_review_helpful',
            'note',
            ['label' => '', 'text' => __('%1 people found this helpful', $review->getAitocReviewHelpful())]
        );

        $fieldset->addField(
            'aitoc_review_unhelpful',
            'note',
            ['label' => '', 'text' => __('%1 people found this unhelpful', $review->getAitocReviewUnhelpful())]
        );

        $fieldset = $form->addFieldset(
            'review_comment',
            ['legend' => __('Review Comment'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'admin_title',
            'text',
            ['label' => __('Title'), 'name' => 'admin_title']
        );

        $fieldset->addField(
            'comment',
            'textarea',
            ['label' => __('Comment'), 'name' => 'comment', 'style' => 'height:24em;']
        );

        $fieldset->addField(
            'send_to',
            'checkbox',
            [
                'name' => 'send_to',
                'label' => __('Send to customer'),
                'title' => __('Send to customer'),
                'onchange' => 'this.value = this.checked;'
            ]
        );

        $form->setUseContainer(true);
        $form->setValues($review->getData());
        $this->setForm($form);
        return $this;
    }
}
