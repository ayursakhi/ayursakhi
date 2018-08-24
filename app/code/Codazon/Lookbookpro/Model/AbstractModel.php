<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    
    protected $_storeValuesFlags = [];
    
    public function getResourceCollection()
    {
        $collection = parent::getResourceCollection()->setStoreId($this->getStoreId());
        return $collection;
    }
    
    public function getAvailableStatuses()
	{
		return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
	}
    
    public function setExistsStoreValueFlag($attributeCode)
    {
        $this->_storeValuesFlags[$attributeCode] = true;
        return $this;
    }
    
	public function getExistsStoreValueFlag($attributeCode)
    {
        return array_key_exists($attributeCode, $this->_storeValuesFlags);
    }
}
