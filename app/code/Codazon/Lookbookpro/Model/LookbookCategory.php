<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

class LookbookCategory extends \Codazon\Lookbookpro\Model\AbstractModel
{

    const ENTITY = 'cdzlookbook_category';
    
    const CACHE_TAG = 'cdzlookbook_category';
    
    const TREE_ROOT_ID = 1;
    
    const KEY_PARENT_ID = 'parent_id';
    
    const KEY_NAME = 'name';
    const KEY_IS_ACTIVE = 'is_active';
    const KEY_POSITION = 'position';
    const KEY_LEVEL = 'level';
    const KEY_UPDATED_AT = 'updated_at';
    const KEY_CREATED_AT = 'created_at';
    const KEY_PATH = 'path';
    
    protected $_eventPrefix = 'cdzlookbook_category';
    
    protected function _construct()
    {
        $this->_init('Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory');
    }
    
    public function getLookbooksPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('lookbooks_position');
        if ($array === null) {
            $array = $this->getResource()->getLookbooksPosition($this);
            $this->setData('lookbooks_position', $array);
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
    
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if ($ids === null) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }
    
    public function getLevel()
    {
        if (!$this->hasLevel()) {
            return count(explode('/', $this->getPath())) - 1;
        }
        return $this->getData(self::KEY_LEVEL);
    }
    
    public function getParentCategories()
    {
        return $this->getResource()->getParentCategories($this);
    }
    
    public function move($parentId, $afterCategoryId)
    {
        /**
         * Validate new parent category id. (category model is used for backward
         * compatibility in event params)
         */
        try {
            $parent = \Magento\Framework\App\ObjectManager::getInstance()->get('Codazon\Lookbookpro\Model\LookbookCategory')->load($parentId);
            
        } catch (NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Sorry, but we can\'t find the new parent category you selected.'
                ),
                $e
            );
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t find the new category you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'We can\'t move the category because the parent category name matches the child category name.'
                )
            );
        }

        /**
         * Setting affected category ids for third party engine index refresh
         */
        $this->setMovedCategoryId($this->getId());
        $oldParentId = $this->getParentId();
        $oldParentIds = $this->getParentIds();

        $eventParams = [
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId,
        ];

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterCategoryId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds([$this->getId(), $oldParentId, $parentId]);
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch('lookbook_category_move', $eventParams);
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        $this->_cacheManager->clean([self::CACHE_TAG]);

        return $this;
    }
    
    public function getParentCategory()
    {
        if (!$this->hasData('parent_category')) {
            $parentId = $this->getParentId();
            $parentCategory = $this->_objectManager->create(self::class);
            $parentCategory->load($parentId);
            $this->setData('parent_category', $parentCategory);
        }
        return $this->_getData('parent_category');
    }
    
    public function getParentId()
    {
        $parentId = $this->getData(self::KEY_PARENT_ID);
        if (isset($parentId)) {
            return $parentId;
        }
        $parentIds = $this->getParentIds();
        return intval(array_pop($parentIds));
    }
    
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), [$this->getId()]);
    }
    
    public function getPathInStore()
    {
        $result = [];
        $path = array_reverse($this->getPathIds());
        foreach ($path as $itemId) {
            if ($itemId == $this->_storeManager->getStore()->getRootCategoryId()) {
                break;
            }
            $result[] = $itemId;
        }
        return implode(',', $result);
    }
        
    public function getPosition()
    {
        return $this->getData(self::KEY_POSITION);
    }
    
    public function getChildrenCount()
    {
        return $this->getData('children_count');
    }
    
    public function __toArray()
    {
        $data = $this->_data;
        $hasToArray = function ($model) {
            return is_object($model) && method_exists($model, '__toArray') && is_callable([$model, '__toArray']);
        };
        foreach ($data as $key => $value) {
            if ($hasToArray($value)) {
                $data[$key] = $value->__toArray();
            } elseif (is_array($value)) {
                foreach ($value as $nestedKey => $nestedValue) {
                    if ($hasToArray($nestedValue)) {
                        $value[$nestedKey] = $nestedValue->__toArray();
                    }
                }
                $data[$key] = $value;
            }
        }
        return $data;
    }
    
    public function getChildren($recursive = false, $isActive = true, $sortByPosition = false)
    {
        return implode(',', $this->getResource()->getChildren($this, $recursive, $isActive, $sortByPosition));
    }
    
    public function beforeDelete()
    {
        if ($this->getResource()->isForbiddenToDelete($this->getId())) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can\'t delete root category.'));
        }
        return parent::beforeDelete();
    }
}
