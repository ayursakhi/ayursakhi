<?php
namespace Aitoc\ReviewBooster\Block\Plugin\Review;

class Form
{
    /**
     * Rewrite template
     *
     * @param \Magento\Review\Block\Form $form
     */
    public function beforeToHtml(\Magento\Review\Block\Form $form)
    {
        $form->setTemplate('Aitoc_ReviewBooster::rewrite/review/view/frontend/templates/form.phtml');
    }
}
