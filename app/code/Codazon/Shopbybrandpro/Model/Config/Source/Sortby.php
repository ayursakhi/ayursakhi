<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Model\Config\Source;

class Sortby implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'brand_label',  'label' => __('Brand Label')],
            ['value' => 'sort_order',   'label' => __('Attribute Option Sort Order')]
        ];
    }	
	
}