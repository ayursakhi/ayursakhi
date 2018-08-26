<?php
namespace Aitoc\ReviewBooster\Block\Plugin\Review\Product\View;

class ListView
{
    /**
     * Rewrite template
     *
     * @param \Magento\Review\Block\Product\View\ListView $listView
     */
    public function beforeToHtml(\Magento\Review\Block\Product\View\ListView $listView)
    {
        $listView->setTemplate('Aitoc_ReviewBooster::rewrite/review/view/frontend/templates/product/view/list.phtml');
    }
}
