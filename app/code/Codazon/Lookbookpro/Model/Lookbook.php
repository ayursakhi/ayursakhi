<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

class Lookbook extends \Codazon\Lookbookpro\Model\AbstractModel
{
    const ENTITY = 'cdzlookbook';
    
    const CACHE_TAG = 'cdzlookbook_lookbook';
    
    const CACHE_LOOKBOOK_CATEGORY_TAG = 'cdzlookbook_category_lookbook';
    
    protected $_cacheTag = self::CACHE_TAG;
    
    protected $_eventPrefix = 'cdzlookbook_lookbook';
    
    protected function _construct()
    {
        $this->_init('Codazon\Lookbookpro\Model\ResourceModel\Lookbook');
    }
    
    public function getItemsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('items_position');
        if ($array === null) {
            $array = $this->getResource()->getItemsPosition($this);
            $this->setData('items_position', $array);
        }
        return $array;
    }
    
    public function getCategoriesPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('categories_position');
        if ($array === null) {
            $array = $this->getResource()->getCategoriesPosition($this);
            $this->setData('categories_position', $array);
        }
        return $array;
    }
    
    public function getCategoryIds() {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('category_ids');
        if ($array === null) {
            $array = [];
            foreach ($this->getCategoriesPosition() as $categoryId => $position) {
                $array[] = (string)$categoryId;
            }
            $this->setData('category_ids', $array);
        }
        
        return $array;
    }
    
    public function getIdentities()
    {
        $identities = [
            self::CACHE_TAG . '_' . $this->getId(),
        ];
        return $identities;
    }
    
}
