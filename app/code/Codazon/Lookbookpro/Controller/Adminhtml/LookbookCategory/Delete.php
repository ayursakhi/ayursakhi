<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookCategory;

use Magento\Backend\App\Action;
use Magento\Store\Model\Store;
use Magento\Framework\App\ObjectManager;

class Delete extends AbstractLookbookCategory
{
    protected $eventName = 'lookbookpro_cdzlookbook_category_prepare_delete';
    protected $_updateMsg = 'This category was deleted.';
    protected $imageUploader = null;
    protected $filterManager = null;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_category_save');
    }
    
    public function execute()
	{
        
        $request = $this->getRequest();
        $data = $request->getParams();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->_objectManager->create($this->modelClass);
            $id = $this->getRequest()->getParam($this->primary);
            $store = (int)$request->getParam('store', Store::DEFAULT_STORE_ID);
            $this->_eventManager->dispatch(
				$this->eventName,
				['model' => $model, 'request' => $this->getRequest()]
			);
            $scopeCode = $store? ObjectManager::getInstance()->get('Magento\Store\Model\Store')->load($store)->getCode() : 'default';
            $scopeConfig = ObjectManager::getInstance()->get('Magento\Framework\App\Config');
            try {
                $model->setStoreId($store)->load($id);
				$result = $model->delete();
                $this->messageManager->addSuccess($this->_updateMsg);
                return $resultRedirect->setPath('*/*/edit', [$this->primary => $scopeConfig->getValue('codazon_lookbook/general/root_category', 'store', $scopeCode)]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\RuntimeException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, $e->getMessage());
			}
			return $resultRedirect->setPath('*/*/edit', [$this->primary => $id, '_current' => true]);
        }
    }
    
}