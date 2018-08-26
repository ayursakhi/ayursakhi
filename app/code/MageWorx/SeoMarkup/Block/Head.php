<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block;

abstract class Head extends \Magento\Framework\View\Element\Template
{
    /**
     * @return string (HTML)
     */
    abstract protected function getMarkupHtml();
}
