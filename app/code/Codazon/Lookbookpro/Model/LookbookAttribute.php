<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

class LookbookAttribute extends \Magento\Eav\Model\Entity\Attribute implements \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface
{
    const MODULE_NAME = 'Codazon_Lookbookpro';
    
    const KEY_IS_GLOBAL = 'is_global';
    
    protected $_eventObject = 'attribute';
    
    protected $_eventPrefix = 'cdzlookbook_entity_attribute';
    
    protected function _construct()
    {
        $this->_init('Codazon\Lookbookpro\Model\ResourceModel\LookbookAttribute');
    }
    
    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }
	
    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }
	
    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }
    
    public function __sleep()
    {
        $this->unsetData('entity_type');
        return parent::__sleep();
    }
    
    public function afterDelete()
    {
        $this->_eavConfig->clear();
        return parent::afterDelete();
    }
    
    public function setScope($scope)
    {
        if ($scope == self::SCOPE_GLOBAL_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_GLOBAL);
        } elseif ($scope == self::SCOPE_WEBSITE_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_WEBSITE);
        } elseif ($scope == self::SCOPE_STORE_TEXT) {
            return $this->setData(self::KEY_IS_GLOBAL, self::SCOPE_STORE);
        } else {
            //Ignore unrecognized scope
            return $this;
        }
    }
    
}
