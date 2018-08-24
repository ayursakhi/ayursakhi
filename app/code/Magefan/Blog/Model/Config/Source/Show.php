<?php
/**
 * Copyright © 2016 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\Blog\Model\Config\Source;

class Show implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray()
    {
        return [
        	['value' => 'thumb', 'label' => __('Featured Image')],
        	['value' => 'name', 'label' => __('Title')],
        	['value' => 'description', 'label' => __('Short Content')],
        	['value' => 'published_date', 'label' => __('Published Date')],
			['value' => 'author', 'label' => __('Author')]
        ];
    }

    public function toArray()
    {
        return $this->toOptionArray();
    }
    
    
}