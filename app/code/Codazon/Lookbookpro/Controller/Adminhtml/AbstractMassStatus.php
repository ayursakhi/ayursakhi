<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Store\Model\Store;

class AbstractMassStatus extends \Magento\Backend\App\Action
{
    protected $primary = 'entity_id';
    protected $collectionClass = 'Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection';
    protected $modelClass = 'Magento\Framework\Model\AbstractModel';
    protected $status = 1;
    
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        
        try {
            if (!empty($selected)) {
                if(!is_array($selected)){
                    $selected = [$selected];
                }
                $this->selectedSetStatus($selected);
            } else {
                $this->messageManager->addError(__('Please select item(s).'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
    
    protected function selectedSetStatus($selected)
    {
        $collection = $this->_objectManager->create($this->modelClass)
            ->getCollection()
            ->setStoreId($this->getRequest()->getParam('store', 0));
        $collection->addFieldToFilter($this->primary, ['in' => $selected]);
        $collection->addAttributeToSelect(['is_active', 'name']);
        $this->setStatus($collection);
        $this->setSuccessMessage($collection->count());
        return $this;
    }
    
    protected function setStatus($collection)
    {        
        $storeId = $this->getRequest()->getParam('store', 0);
        foreach ($collection->getItems() as $item) {
            $item->setData('is_active', (string)$this->status);
            $item->setStoreId($storeId);
            $item->save();
        }
        return $this;
    }
    
    protected function setSuccessMessage($count)
    {
		$this->messageManager->addSuccess(__('A total of %1 record(s) have been changed status.', $count));
        return $this;
	}
}
