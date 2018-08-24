<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Codazon\AjaxLayeredNav\Block;

class Json extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Codazon\AjaxLayeredNav\Block\Block $lib,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $coreRegistry;
        $this->_categoryFactory = $categoryFactory;
        $this->_lib = $lib;
    }
    
    public function toHtml()
    {
        $data = [];
        $layer = $this->getLayout()->getBlock('catalog.leftnav');
        $products = $this->getLayout()->getBlock('category.products');
        $brandInfo = $this->getLayout()->getBlock('brand.information');

        $category = $layer->getLayer()->getCurrentCategory();
        $hasChildren = $category->hasChildren();
        if ($category->getIsAnchor()) {
            $type = $hasChildren ? 'layered' : 'layered_without_children';
        } else {
            $type = $hasChildren ? 'default' : 'default_without_children';
        }

        $data = array(
            'products' => $products->toHtml()
        );
        if($type != 'default' && $type != 'default_without_children'){
            $data['layer'] = $layer->toHtml();
        }
        if ($brandInfo) {
            $data['brandInfo'] = $brandInfo->toHtml();
        }
        $json = json_encode($data);
        return $json;
    }
}
