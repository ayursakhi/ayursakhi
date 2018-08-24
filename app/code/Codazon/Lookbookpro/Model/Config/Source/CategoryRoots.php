<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\Config\Source;

use Magento\Framework\App\ObjectManager;

class CategoryRoots implements \Magento\Framework\Option\ArrayInterface
{
    protected $_options;
    
    protected $_categoryFactory;
    
    protected $_request;
    
    public function getCategoryFactory()
    {
        if ($this->_categoryFactory === null) {
            $this->_categoryFactory = ObjectManager::getInstance()
                ->get('Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory');
        }
        return $this->_categoryFactory;
    }
    
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->_request = ObjectManager::getInstance()->get('Magento\Framework\App\RequestInterface');
        }
        return $this->_request;
    }
    
    protected function _getOptions()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $collection = $this->getCategoryFactory()->create();
        $collection
            ->setStoreId($storeId)
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToSelect(['name'])
            ->addFieldToFilter('parent_id', \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID)
            ->getSelect();
        $options = [];
        if ($collection->count()) {
            foreach ($collection as $category) {
                $options[] = [
                    'label' => $category->getName(),
                    'value' => $category->getId()
                ];
            }
        }
        return $options;
    }
    
    
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = $this->_getOptions();
        }
        return $this->_options;
    }
}