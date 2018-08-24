<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\LookbookItem;

use Codazon\Lookbookpro\Model\ResourceModel\LookbookItem\CollectionFactory as CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Ui\Component\Form\Field;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{   
    protected $collection;
    
    protected $dataPersistor;
    
    protected $request;
    
    protected $loadedData;
    
    protected $storeId;
    
    protected $registry;
    
    protected $_isEav = true;
    
    protected $imageUploader = null;
    
    protected $requestScopeFieldName = 'store';
    
    protected $entityType = null;
    
    protected $_entityTypeCode = 'cdzlookbook_item';
    
    private $eavConfig;

    /**
     * Form element mapping
     *
     * @var array
     */
    protected $formElement = [
        'text' => 'input',
        'boolean' => 'checkbox',
    ];
    protected $metaProperties = [
        'dataType' => 'frontend_input',
        'visible' => 'is_visible',
        'required' => 'is_required',
        'label' => 'frontend_label',
        'sortOrder' => 'sort_order',
        'notice' => 'note',
        'default' => 'default_value',
        'size' => 'multiline_count',
    ];
    
	public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        Config $eavConfig,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->storeId = $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID);
        $this->eavConfig = $eavConfig;
        $this->entityType = $this->eavConfig->getEntityType($this->_entityTypeCode);
        if ($this->_isEav) {
            $this->collection->addAttributeToSelect('*');
        }
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->meta = $this->prepareMeta($this->meta);
    }
    
	public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        
        $item = $this->getCurrentItem();
        $data = $item->getData();
        $data = $this->addUseDefaultSettings($item, $data);
        $data = $this->filterFields($data);
        $this->loadedData[$item->getId()] = $data;
        $data = $this->dataPersistor->get('cdzlookbook_item');
        if (!empty($data)) {
            $item = $this->collection->getNewEmptyItem();
            $item->setData($data);
            $this->loadedData[$item->getId()] = $item->getData();
            $this->dataPersistor->clear('cdzlookbook_item');
        }
        return $this->loadedData;
    }
    
    public function getCurrentItem()
    {
        $item = $this->registry->registry('lookbookpro_cdzlookbook_item');
        if ($item) {
            return $item;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        $requestScope = $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID);
        if ($requestId) {
            $item = $this->collection->addFieldToFilter($this->primaryFieldName, $requestId)->getFirstItem();
            if (!$item->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $item;
    }
    
    protected function addUseDefaultSettings($item, $data)
    {
        $attributeCollection = $this->entityType->getAttributeCollection();
        foreach($attributeCollection as $attribute) {
            $code = $attribute->getAttributeCode();
            if ($item->getExistsStoreValueFlag($code) ||
                $item->getStoreId() === Store::DEFAULT_STORE_ID
            ) {
                $data['isUseDefault'][$code] = false;
            } else {
                $data['isUseDefault'][$code] = true;
            }
        }
        return $data;
    }
    
    protected function filterFields(array $rawData)
    {
        $data = $rawData;
        $imagesType = ['thumbnail', 'cover'];
        
        foreach ($imagesType as $image)
        {
            if (isset($data[$image])) {
                $imageName = (string)$data[$image];
                unset($data[$image]);
                $data[$image][0]['name'] = $imageName;
                $data[$image][0]['url'] = $this->_getImageUrl($imageName);
            }
        }
        return $data;
    }
    
    protected function _getImageUrl($imageName) {
        return $this->getImageUploader()->getImageUrl($imageName);
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
            $this->imageUploader = \Magento\Framework\App\ObjectManager::getInstance()->get(
                'Codazon\Lookbookpro\CategoryImageUpload'
            );
        }
        return $this->imageUploader;
    }
        
     /**
     * Prepare meta data
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta($meta)
    {
        $meta = array_replace_recursive($meta, $this->prepareFieldsMeta(
            $this->getFieldsMap(),
            $this->getAttributesMeta($this->entityType)
        ));
        return $meta;
    }
    
    private function prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }
    
    /**
     * @return array
     */
    protected function getFieldsMap()
    {
        return [
            'general' => [
                'name',
                'is_active',
                //'description'
            ],
            'photo_config' => [
                'item_data',
            ]
        ];
    }
    
    public function getAttributesMeta(Type $entityType)
    {
        $meta = [];
        $attributes = $entityType->getAttributeCollection();
        $isScopeStore = $this->request->getParam($this->requestScopeFieldName, Store::DEFAULT_STORE_ID) != Store::DEFAULT_STORE_ID;
        foreach ($attributes as $attribute) {
            $code = $attribute->getAttributeCode();           
            $meta[$code]['scopeLabel'] = $this->getScopeLabel($attribute);
            $meta[$code]['componentType'] = Field::NAME;
            
            if ($isScopeStore) {
                $meta[$code]['imports'] = [
                    'isUseDefault' => '${ $.provider }:data.isUseDefault.'.$code
                ];
                $meta[$code]['service'] = [
                    'template' => 'ui/form/element/helper/service'
                ];
            }
        }
        $result = [];
        foreach ($meta as $key => $item) {
            $result[$key] = $item;
        }
        $result = $this->getDefaultMetaData($result);
        return $result;
    }
    
    public function getScopeLabel($attribute)
    {
        $html = '';
        if (!$attribute || $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return $html;
        }
        if ($attribute->isScopeGlobal()) {
            $html .= __('[GLOBAL]');
        } elseif ($attribute->isScopeWebsite()) {
            $html .= __('[WEBSITE]');
        } elseif ($attribute->isScopeStore()) {
            $html .= __('[STORE VIEW]');
        }

        return $html;
    }
    
    public function getDefaultMetaData($result)
    {
        return $result;
    }
}