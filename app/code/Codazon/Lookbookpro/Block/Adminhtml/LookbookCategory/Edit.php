<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * Return URL for refresh input element 'path' in form
     *
     * @return string
     * @since 101.0.0
     */
    public function getRefreshPathUrl()
    {
        return $this->getUrl('lookbookpro/lookbookcategory/refreshPath', ['_current' => true]);
    }
}
