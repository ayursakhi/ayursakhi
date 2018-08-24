<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Model\Config\Source;

class SortOrder implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'asc',      'label' => __('ASC')],
            ['value' => 'desc',     'label' => __('DESC')]
        ];
    }	
	
}