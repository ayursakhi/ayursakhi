<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookCategory;

use Magento\Backend\App\Action;
use \Magento\Store\Model\Store;

class Save extends AbstractLookbookCategory
{
    protected $eventName = 'lookbookpro_cdzlookbook_category_prepare_save';
    
    protected $_updateMsg = 'You saved this Lookbook Category.';
    
    protected $imageUploader = null;
    
    protected $filterManager = null;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_category_save');
    }
    
    protected function getParentCategory($parentId, $storeId)
    {
        if (!$parentId) {
            $parentId = \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
        }
        return $this->_objectManager->create($this->modelClass)->load($parentId);
    }
    
    public function execute()
	{
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $isNewCategory = !isset($categoryPostData['entity_id']);
            $data = $this->imagePreprocessing($data);
            $data = $this->_filterData($data);
            $model = $this->_objectManager->create($this->modelClass);
            $id = $this->getRequest()->getParam($this->primary);
            $store = (int)$request->getParam('store', Store::DEFAULT_STORE_ID);
            
            if (isset($data['lookbooks'])
                && is_string($data['lookbooks'])
                && !$model->getLookbooksReadonly()
            ) {
                $lookbooks = json_decode($data['lookbooks'], true);
                $model->setPostedLookbooks($lookbooks);
            }
            if ($id) {
				$model->setStoreId($store)->load($id);
                // if (isset($data['parent_id'])) {
                    // unset($data['parent_id']);
                // }
			} else {
                unset($data[$this->primary]);
                if (isset($data['parent_id'])) {
                    $parentId = $data['parent_id'];
                    if ($isNewCategory) {
                        $parentCategory = $this->getParentCategory($parentId, $store);
                        $model->setPath($parentCategory->getPath());
                        $model->setParentId($parentCategory->getId());
                    }
                }
            }
            $data['store_id'] = $store;
            
            if($data['store_id'] == Store::DEFAULT_STORE_ID) {
                if (empty($data['url_key'])) {
                    $data['url_key'] = $this->formatUrlKey($data['name']);
                }
            }

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
                $returnParams = [$this->primary => $model->getId(), '_current' => true, 'parent' => false, 'back' => false];
                if ($store) {
                    $returnParams['store'] = $store;
                } else {
                    $returnParams['store'] = false;
                }
                return $resultRedirect->setPath('*/*/edit', $returnParams);
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
                        $this->_moveFileFromTmp($data[$image]);
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
                'Codazon\Lookbookpro\CategoryImageUpload'
            );
        }
        return $this->imageUploader;
    }
    
    protected function _moveFileFromTmp($image)
    {
        $this->getImageUploader()->moveFileFromTmp($image);
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
