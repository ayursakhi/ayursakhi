<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Model\Config\Source;

class AttributeCode implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        $collection = \Magento\Framework\App\ObjectManager::getInstance()->get('Codazon\Shopbybrandpro\Model\ResourceModel\SelectedBrands');
		return $collection->getAttributeCodeList();
    }	
	
}
