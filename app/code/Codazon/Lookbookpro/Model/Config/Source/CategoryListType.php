<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\Config\Source;

use Magento\Framework\App\ObjectManager;

class CategoryListType implements \Magento\Framework\Option\ArrayInterface
{
    
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => (string)__('All categories')],
            ['value' => '2', 'label' => (string)__('Sub categories')]
        ];
    }
}