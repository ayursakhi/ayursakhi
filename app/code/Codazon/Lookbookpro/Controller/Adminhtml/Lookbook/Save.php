<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;
use \Magento\Store\Model\Store;

class Save extends AbstractLookbook
{
    protected $eventName = 'lookbookpro_cdzlookbook_prepare_save';
    protected $_updateMsg = 'You saved this Lookbook.';
    protected $imageUploader = null;
    protected $filterManager = null;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_save');
    }
    
    public function execute()
	{
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            
            $data = $this->imagePreprocessing($data);
            $data = $this->_filterData($data);
            $model = $this->_objectManager->create($this->modelClass);
            $id = $this->getRequest()->getParam($this->primary);
            $store = (int)$request->getParam('store', Store::DEFAULT_STORE_ID);
            if (isset($data['category_ids'])) {
                if (!is_array($data['category_ids'])) {
                    $data['category_ids'] = explode(',', $data['category_ids']);
                }
            }
            
            if (isset($data['lookbook_items'])
                && is_string($data['lookbook_items'])
                && !$model->getLookbookItemsReadonly()
            ) {
                $items = json_decode($data['lookbook_items'], true);
                $model->setPostedItems($items);
            }
            
            if ($id) {
				$model->setStoreId($store)->load($id);
			} else {
                unset($data[$this->primary]);
            }
            $data['store_id'] = $store;
            
            if ($data['store_id'] == Store::DEFAULT_STORE_ID) {
                if (empty($data['url_key'])) {
                    $data['url_key'] = $this->formatUrlKey($data['name']);
                }
            }
            
            $data['url_key'] = $this->getValidUrlKey($model, $data['url_key'], $store);

            if (isset($data['use_default']) && is_array($data['use_default'])) {
                foreach ($data['use_default'] as $attributeCode => $useDefault) {
                    if ($useDefault) {
                        $data[$attributeCode] = false;
                    }
                }
            }
           
            $model->addData($data);          
            
            $this->_eventManager->dispatch(
				$this->eventName,
				['model' => $model, 'request' => $this->getRequest()]
			);
            
            try {
				$result = $model->save();
                
                $this->messageManager->addSuccess($this->_updateMsg);
                if ($request->getParam('back') == 'edit') {
                    $returnParams = [$this->primary => $model->getId(), '_current' => true, 'back' => false];
                    if ($store) {
                        $returnParams['store'] = $store;
                    }
					return $resultRedirect->setPath('*/*/edit', $returnParams);
				} elseif ($request->getParam('back') == 'new') {
                    return $resultRedirect->setPath('*/*/new', []);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
                
			} catch (\RuntimeException $e) {
				$this->messageManager->addError($e->getMessage());
                
			} catch (\Exception $e) {
                
				$this->messageManager->addException($e, $e->getMessage());
			}
            
            $this->_getSession()->setFormData($data);
			return $resultRedirect->setPath('*/*/edit', [$this->primary => $this->getRequest()->getParam($this->primary)]);
        }
    }
    
    protected function _filterData(array $rawData)
    {
        $data = $rawData;
        $imagesType = ['thumbnail', 'cover'];
        
        foreach ($imagesType as $image)
        {
            if (isset($data[$image]) && is_array($data[$image])) {
                if (!empty($data[$image]['delete'])) {
                    $data[$image] = false;
                } else {
                    if (isset($data[$image][0]['name']) && isset($data[$image][0]['tmp_name'])) {
                        $data[$image] = $data[$image][0]['name'];
                        $data[$image] = $this->_moveFileFromTmp($data[$image]);
                    } else {
                        unset($data[$image]);
                    }
                }
            }
        }
        return $data;
    }
    
    /**
     * Get image uploader
     *
     * @return \Codazon\Lookbookpro\Model\ImageUploader
     *
     * @deprecated
     */
    private function getImageUploader()
    {
        if ($this->imageUploader === null) {
            $this->imageUploader = $this->_objectManager->get(
                'Codazon\Lookbookpro\ItemImageUpload'
            );
        }
        return $this->imageUploader;
    }
    
    protected function _moveFileFromTmp($image)
    {
        return $this->getImageUploader()->moveFileFromTmp($image);
    }
    
    public function imagePreprocessing($data)
    {
        $imagesType = ['thumbnail', 'cover'];
        foreach ($imagesType as $image) {
            if (empty($data[$image])) {
                unset($data[$image]);
                $data[$image]['delete'] = true;
            }
        }
        return $data;
    }
    
    public function formatUrlKey($str)
    {
        return $this->getFilterManager()->translitUrl($str);
    }
    
    public function getValidUrlKey($model, $urlKey, $store)
    {
        $i = 0;
        do {
            $count = $this->_objectManager->create($this->modelClass)->getCollection()
                    ->addFieldToFilter('entity_id', ['neq' => $model->getId()])
                    ->setStoreId($store)
                    ->addAttributeToFilter('url_key', $urlKey)
                    ->count();
            if ($count > 0) {
                $i++;
                $urlKey = $urlKey . '-' . $i;
            }
        } while ($count > 0);
        return $urlKey;
    }
    
    
    
    public function getFilterManager()
    {
        if ($this->filterManager === null) {
            $this->filterManager = $this->_objectManager->get(
                'Magento\Framework\Filter\FilterManager'
            );
        }
        return $this->filterManager;
    }   
}
