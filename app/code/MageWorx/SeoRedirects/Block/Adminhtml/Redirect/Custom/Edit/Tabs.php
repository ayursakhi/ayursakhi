<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageWorx\SeoRedirects\Block\Adminhtml\Redirect\Custom\Edit;

class Tabs extends \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tabs
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mageworx_custom_redirect_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Custom Redirect'));
    }
}
