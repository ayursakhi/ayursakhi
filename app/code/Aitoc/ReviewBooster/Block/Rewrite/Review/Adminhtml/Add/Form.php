<?php
namespace Aitoc\ReviewBooster\Block\Rewrite\Review\Adminhtml\Add;

class Form extends \Magento\Review\Block\Adminhtml\Add\Form
{
    /**
     * Prepare add review form
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('add_review_form', ['legend' => __('Review Details')]);

        $fieldset->addField('product_name', 'note', ['label' => __('Product'), 'text' => 'product_name']);

        $fieldset->addField(
            'detailed_rating',
            'note',
            [
                'label' => __('Product Rating'),
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
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'select_stores',
                'multiselect',
                [
                    'label' => __('Visibility'),
                    'required' => true,
                    'name' => 'select_stores[]',
                    'values' => $this->_systemStore->getStoreValuesForForm()
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'nickname',
            'text',
            [
                'name' => 'nickname',
                'title' => __('Nickname'),
                'label' => __('Nickname'),
                'maxlength' => '50',
                'required' => true
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'title' => __('Summary of Review'),
                'label' => __('Summary of Review'),
                'maxlength' => '255',
                'required' => true
            ]
        );

        $fieldset->addField(
            'detail',
            'textarea',
            [
                'name' => 'detail',
                'title' => __('Review'),
                'label' => __('Review'),
                'required' => true
            ]
        );

        $fieldset->addField('product_id', 'hidden', ['name' => 'product_id']);

        /*$gridFieldset = $form->addFieldset('add_review_grid', array('legend' => __('Please select a product')));
          $gridFieldset->addField('products_grid', 'note', array(
          'text' => $this->getLayout()->createBlock('Magento\Review\Block\Adminhtml\Product\Grid')->toHtml(),
          ));*/

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

        $fieldset = $form->addFieldset('add_review_comment', ['legend' => __('Review Comment')]);

        $fieldset->addField(
            'admin_title',
            'text',
            [
                'name' => 'admin_title',
                'title' => __('Title'),
                'label' => __('Title'),
                'maxlength' => '50'
            ]
        );

        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'comment',
                'title' => __('Comment'),
                'label' => __('Comment')
            ]
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

        $form->setMethod('post');
        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('review/product/post'));

        $this->setForm($form);
    }
}
