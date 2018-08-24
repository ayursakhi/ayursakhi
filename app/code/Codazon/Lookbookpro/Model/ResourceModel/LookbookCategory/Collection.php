<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory;


class Collection extends \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
{
    protected function _construct() {
        $this->_init('Codazon\Lookbookpro\Model\LookbookCategory', 'Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory');
    }
    
    public function addIdFilter($categoryIds)
    {
        if (is_array($categoryIds)) {
            if (empty($categoryIds)) {
                $condition = '';
            } else {
                $condition = ['in' => $categoryIds];
            }
        } elseif (is_numeric($categoryIds)) {
            $condition = $categoryIds;
        } elseif (is_string($categoryIds)) {
            $ids = explode(',', $categoryIds);
            if (empty($ids)) {
                $condition = $categoryIds;
            } else {
                $condition = ['in' => $ids];
            }
        }
        $this->addFieldToFilter('entity_id', $condition);
        return $this;
    }
}
