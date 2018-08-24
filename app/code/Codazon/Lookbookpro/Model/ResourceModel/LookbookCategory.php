<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\ResourceModel;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class LookbookCategory extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Model factory
     *
     * @var \Magento\Catalog\Model\Factory
     */
    protected $_modelFactory;
    
    protected $_lookbookCategoryTable;
    
    protected $_categoryCollectionFactory;
    
    protected $_isActiveAttributeId = null;

    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Codazon\Lookbookpro\Model\LookbookCategoryFactory $modelFactory,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory $categoryCollectionFactory,
        $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_modelFactory = $modelFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->connectionName  = 'cdzlookbook_category';
        parent::__construct($context, $data);
    }
    
    protected function _construct()
    {
        $this->_read = 'cdzlookbook_category_read';
        $this->_write = 'cdzlookbook_category_write';
    }
    
    public function getEntityType()
    {
        if(empty($this->_type)) {
            $this->setType(\Codazon\Lookbookpro\Model\LookbookCategory::ENTITY);
        }
        return parent::getEntityType();
    }
    
    protected function _getDefaultAttributes()
    {
        return ['created_at', 'updated_at', 'parent_id', 'path', 'position', 'level', 'children_count'];
    }
    
    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }
	
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            return $this->_storeManager->getStore()->getId();
        }
        return $this->_storeId;
    }
    
    public function findWhereAttributeIs($entityIdsFilter, $attribute, $expectedValue)
    {
	    $bind = ['attribute_id' => $attribute->getId(), 'value' => $expectedValue];
        $select = $this->getConnection()->select()->from(
            $attribute->getBackend()->getTable(),
            ['entity_id']
        )->where(
            'attribute_id = :attribute_id'
        )->where(
            'value = :value'
        )->where(
            'entity_id IN(?)',
            $entityIdsFilter
        );

        return $this->getConnection()->fetchCol($select, $bind);
    }
    
    public function verifyIds(array $ids)
    {       
	    if (empty($ids)) {
            return [];
        }

        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'entity_id'
        )->where(
            'entity_id IN(?)',
            $ids
        );

        return $this->getConnection()->fetchCol($select);
    }
    
    public function getIsActiveAttributeId()
    {
        if ($this->_isActiveAttributeId === null) {
            $this->_isActiveAttributeId = (int)$this->_eavConfig
                ->getAttribute($this->getEntityType(), 'is_active')
                ->getAttributeId();
        }
        return $this->_isActiveAttributeId;
    }
    
    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param \Magento\Framework\DataObject $object
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return boolean
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        $applyTo = $attribute->getApplyTo();
        return (count($applyTo) == 0 || in_array($object->getTypeId(), $applyTo))
            && $attribute->isInSet($object->getAttributeSetId());
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param AbstractAttribute|\Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend|\Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend|\Magento\Eav\Model\Entity\Attribute\Source\AbstractSource $instance
     * @param string $method
     * @param array $args array of arguments
     * @return boolean
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if ($instance instanceof \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
            && ($method == 'beforeSave' || $method = 'afterSave')
        ) {
            $attributeCode = $instance->getAttribute()->getAttributeCode();
            if (isset($args[0]) && $args[0] instanceof \Magento\Framework\DataObject && $args[0]->getData($attributeCode) === false) {
                return false;
            }
        }

        return parent::_isCallableAttributeInstance($instance, $method, $args);
    }

    /**
     * Retrieve select object for loading entity attributes values
     * Join attribute store value
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        /**
         * This condition is applicable for all cases when we was work in not single
         * store mode, customize some value per specific store view and than back
         * to single store mode. We should load correct values
         */
        if ($this->_storeManager->hasSingleStore()) {
            $storeId = (int) $this->_storeManager->getStore(true)->getId();
        } else {
            $storeId = (int) $object->getStoreId();
        }

        $storeIds = [$this->getDefaultStoreId()];
        if ($storeId != $this->getDefaultStoreId()) {
            $storeIds[] = $storeId;
        }

        $select = $this->getConnection()
            ->select()
            ->from(['attr_table' => $table], [])
            ->where("attr_table.{$this->getEntityIdField()} = ?", $object->getId())
            ->where('attr_table.store_id IN (?)', $storeIds);
        return $select;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param array $selects
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        $select = parent::_prepareLoadSelect($selects);
        $select->order('store_id');
        return $select;
    }

    /**
     * Initialize attribute value for object
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param array $valueRow
     * @return $this
     */
    protected function _setAttributeValue($object, $valueRow)
    {
        $attribute = $this->getAttribute($valueRow['attribute_id']);
        if ($attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $isDefaultStore = $valueRow['store_id'] == $this->getDefaultStoreId();
            if (isset($this->_attributes[$valueRow['attribute_id']])) {
                if ($isDefaultStore) {
                    $object->setAttributeDefaultValue($attributeCode, $valueRow['value']);
                } else {
                    $object->setAttributeDefaultValue(
                        $attributeCode,
                        $this->_attributes[$valueRow['attribute_id']]['value']
                    );
                }
            } else {
                $this->_attributes[$valueRow['attribute_id']] = $valueRow;
            }

            $value = $valueRow['value'];
            $valueId = $valueRow['value_id'];

            $object->setData($attributeCode, $value);
            if (!$isDefaultStore) {
                $object->setExistsStoreValueFlag($attributeCode);
            }
            $attribute->getBackend()->setEntityValueId($object, $valueId);
        }

        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $connection = $this->getConnection();
        $storeId = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        $table = $attribute->getBackend()->getTable();

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        if ($this->_storeManager->hasSingleStore()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $table,
                [
                    'attribute_id = ?' => $attribute->getAttributeId(),
                    'entity_id = ?' => $object->getEntityId(),
                    'store_id <> ?' => $storeId
                ]
            );
        }

        $data = new \Magento\Framework\DataObject(
            [
                'attribute_id' => $attribute->getAttributeId(),
                'store_id' => $storeId,
                'entity_id' => $object->getEntityId(),
                'value' => $this->_prepareValueForSave($value, $attribute),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * Insert entity attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        /**
         * save required attributes in global scope every time if store id different from default
         */
        $storeId = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        if ($this->getDefaultStoreId() != $storeId) {
            if ($attribute->getIsRequired() || $attribute->getIsRequiredInAdminStore()) {
                $table = $attribute->getBackend()->getTable();

                $select = $this->getConnection()->select()
                    ->from($table)
                    ->where('attribute_id = ?', $attribute->getAttributeId())
                    ->where('store_id = ?', $this->getDefaultStoreId())
                    ->where('entity_id = ?', $object->getEntityId());
                $row = $this->getConnection()->fetchOne($select);

                if (!$row) {
                    $data = new \Magento\Framework\DataObject(
                        [
                            'attribute_id' => $attribute->getAttributeId(),
                            'store_id' => $this->getDefaultStoreId(),
                            'entity_id' => $object->getEntityId(),
                            'value' => $this->_prepareValueForSave($value, $attribute),
                        ]
                    );
                    $bind = $this->_prepareDataForTable($data, $table);
                    $this->getConnection()->insertOnDuplicate($table, $bind, ['value']);
                }
            }
        }

        return $this->_saveAttributeValue($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @param AbstractAttribute $attribute
     * @param mixed $valueId
     * @param mixed $value
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
		return $this->_saveAttributeValue($object, $attribute, $value);
    }

    /**
     * Update attribute value for specific store
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param object $attribute
     * @param mixed $value
     * @param int $storeId
     * @return $this
     */
    protected function _updateAttributeForStore($object, $attribute, $value, $storeId)
    {
		$connection = $this->getConnection();
        $table = $attribute->getBackend()->getTable();
        $entityIdField = $attribute->getBackend()->getEntityIdField();
        $select = $connection->select()
            ->from($table, 'value_id')
            ->where("$entityIdField = :entity_field_id")
            ->where('store_id = :store_id')
            ->where('attribute_id = :attribute_id');
        $bind = [
            'entity_field_id' => $object->getId(),
            'store_id' => $storeId,
            'attribute_id' => $attribute->getId(),
        ];
        $valueId = $connection->fetchOne($select, $bind);
        /**
         * When value for store exist
         */
        if ($valueId) {
            $bind = ['value' => $this->_prepareValueForSave($value, $attribute)];
            $where = ['value_id = ?' => (int) $valueId];

            $connection->update($table, $bind, $where);
        } else {
            $bind = [
                $entityIdField => (int) $object->getId(),
                'attribute_id' => (int) $attribute->getId(),
                'value' => $this->_prepareValueForSave($value, $attribute),
                'store_id' => (int) $storeId,
            ];

            $connection->insert($table, $bind);
        }

        return $this;
    }

    /**
     * Delete entity attribute values
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @param array $info
     * @return $this
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $connection = $this->getConnection();
        $entityIdField = $this->getEntityIdField();
        $globalValues = [];
        $websiteAttributes = [];
        $storeAttributes = [];

        /**
         * Separate attributes by scope
         */
        foreach ($info as $itemData) {
            $attribute = $this->getAttribute($itemData['attribute_id']);
            if ($attribute->isScopeStore()) {
                $storeAttributes[] = (int) $itemData['attribute_id'];
            } elseif ($attribute->isScopeWebsite()) {
                $websiteAttributes[] = (int) $itemData['attribute_id'];
            } elseif ($itemData['value_id'] !== null) {
                $globalValues[] = (int) $itemData['value_id'];
            }
        }

        /**
         * Delete global scope attributes
         */
        if (!empty($globalValues)) {
            $connection->delete($table, ['value_id IN (?)' => $globalValues]);
        }

        $condition = [
            $entityIdField . ' = ?' => $object->getId(),
        ];

        /**
         * Delete website scope attributes
         */
        if (!empty($websiteAttributes)) {
            $storeIds = $object->getWebsiteStoreIds();
            if (!empty($storeIds)) {
                $delCondition = $condition;
                $delCondition['attribute_id IN(?)'] = $websiteAttributes;
                $delCondition['store_id IN(?)'] = $storeIds;

                $connection->delete($table, $delCondition);
            }
        }

        /**
         * Delete store scope attributes
         */
        if (!empty($storeAttributes)) {
            $delCondition = $condition;
            $delCondition['attribute_id IN(?)'] = $storeAttributes;
            $delCondition['store_id = ?'] = (int) $object->getStoreId();

            $connection->delete($table, $delCondition);
        }

        return $this;
    }

    
    protected function _getDefaultAttributeModel()
    {
        return 'Codazon\Lookbookpro\Model\LookbookCategoryAttribute';
    }
    
    /**
     * Retrieve Object instance with original data
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Framework\DataObject
     */
    protected function _getOrigObject($object)
    {
        $className = get_class($object);
        $origObject = $this->_modelFactory->create();
        $origObject->setData([]);
        $origObject->setStoreId($object->getStoreId());
        $this->load($origObject, $object->getData($this->getEntityIdField()));

        return $origObject;
    }

    /**
     * Check is attribute value empty
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isAttributeValueEmpty(AbstractAttribute $attribute, $value)
    {
        return $value === false;
    }

    /**
     * Return if attribute exists in original data array.
     * Checks also attribute's store scope:
     * We should insert on duplicate key update values if we unchecked 'STORE VIEW' checkbox in store view.
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value New value of the attribute.
     * @param array &$origData
     * @return bool
     */
    protected function _canUpdateAttribute(AbstractAttribute $attribute, $value, array &$origData)
    {
        $result = parent::_canUpdateAttribute($attribute, $value, $origData);
        if ($result
            && ($attribute->isScopeStore() || $attribute->isScopeWebsite())
            && !$this->_isAttributeValueEmpty($attribute, $value)
            && $value == $origData[$attribute->getAttributeCode()]
            && isset($origData['store_id'])
            && $origData['store_id'] != $this->getDefaultStoreId()
        ) {
            return false;
        }

        return $result;
    }

    /**
     * Retrieve attribute's raw value from DB.
     *
     * @param int $entityId
     * @param int|string|array $attribute atrribute's ids or codes
     * @param int|\Magento\Store\Model\Store $store
     * @return bool|string|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributeRawValue($entityId, $attribute, $store)
    {
        if (!$entityId || empty($attribute)) {
            return false;
        }
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $attributesData = [];
        $staticAttributes = [];
        $typedAttributes = [];
        $staticTable = null;
        $connection = $this->getConnection();

        foreach ($attribute as $item) {
            /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */
            $item = $this->getAttribute($item);
            if (!$item) {
                continue;
            }
            $attributeCode = $item->getAttributeCode();
            $attrTable = $item->getBackend()->getTable();
            $isStatic = $item->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attributeCode;
                $staticTable = $attrTable;
            } else {
                /**
                 * That structure needed to avoid farther sql joins for getting attribute's code by id
                 */
                $typedAttributes[$attrTable][$item->getId()] = $attributeCode;
            }
        }

        /**
         * Collecting static attributes
         */
        if ($staticAttributes) {
            $select = $connection->select()->from(
                $staticTable,
                $staticAttributes
            )->where(
                $this->getEntityIdField() . ' = :entity_id'
            );
            $attributesData = $connection->fetchRow($select, ['entity_id' => $entityId]);
        }

        /**
         * Collecting typed attributes, performing separate SQL query for each attribute type table
         */
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = $store->getId();
        }

        $store = (int) $store;
        if ($typedAttributes) {
            foreach ($typedAttributes as $table => $_attributes) {
                $select = $connection->select()
                    ->from(['default_value' => $table], ['attribute_id'])
                    ->where('default_value.attribute_id IN (?)', array_keys($_attributes))
                    ->where('default_value.entity_id = :entity_id')
                    ->where('default_value.store_id = ?', 0);

                $bind = ['entity_id' => $entityId];

                if ($store != $this->getDefaultStoreId()) {
                    $valueExpr = $connection->getCheckSql(
                        'store_value.value IS NULL',
                        'default_value.value',
                        'store_value.value'
                    );
                    $joinCondition = [
                        $connection->quoteInto('store_value.attribute_id IN (?)', array_keys($_attributes)),
                        'store_value.entity_id = :entity_id',
                        'store_value.store_id = :store_id',
                    ];

                    $select->joinLeft(
                        ['store_value' => $table],
                        implode(' AND ', $joinCondition),
                        ['attr_value' => $valueExpr]
                    );

                    $bind['store_id'] = $store;
                } else {
                    $select->columns(['attr_value' => 'value'], 'default_value');
                }

                $result = $connection->fetchPairs($select, $bind);
                foreach ($result as $attrId => $value) {
                    $attrCode = $typedAttributes[$table][$attrId];
                    $attributesData[$attrCode] = $value;
                }
            }
        }

        if (sizeof($attributesData) == 1) {
            $_data = each($attributesData);
            $attributesData = $_data[1];
        }

        return $attributesData ? $attributesData : false;
    }

    /**
     * Reset firstly loaded attributes
     *
     * @param \Magento\Framework\DataObject $object
     * @param integer $entityId
     * @param array|null $attributes
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        $this->_attributes = [];
        return parent::load($object, $entityId, $attributes);
    }
    
    protected function _afterSave(\Magento\Framework\DataObject $object)
    {
        
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }
        $this->_saveCategoryLookbooks($object);        
        return parent::_afterSave($object);
    }
    
    protected function _beforeSave(\Magento\Framework\DataObject $object)
    {
        parent::_beforeSave($object);
        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }
        if ($object->isObjectNew()) {
            if ($object->getPosition() === null) {
                $object->setPosition($this->_getMaxPosition($object->getPath()) + 1);
            }
            $path = explode('/', $object->getPath());
            $level = count($path)  - ($object->getId() ? 1 : 0);
            $toUpdateChild = array_diff($path, [$object->getId()]);

            if (!$object->hasPosition()) {
                $object->setPosition($this->_getMaxPosition(implode('/', $toUpdateChild)) + 1);
            }
            if (!$object->hasLevel()) {
                $object->setLevel($level);
            }
            if (!$object->hasParentId() && $level) {
                $object->setParentId($path[$level - 1]);
            }
            if (!$object->getId()) {
                $object->setPath($object->getPath() . '/');
            }

            $this->getConnection()->update(
                $this->getEntityTable(),
                ['children_count' => new \Zend_Db_Expr('children_count+1')],
                ['entity_id IN(?)' => $toUpdateChild]
            );
        }
        $this->_proccessUrlPath($object);
    }
    
    protected function _savePath($object)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getEntityTable(),
                ['path' => $object->getPath()],
                ['entity_id = ?' => $object->getId()]
            );
            $object->unsetData('path_ids');
        }
        return $this;
    }
    
    protected function _getMaxPosition($path)
    {
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');
        $level = count(explode('/', $path));
        $bind = ['c_level' => $level, 'c_path' => $path . '/%'];
        $select = $connection->select()->from(
            $this->getTable('cdzlookbook_category_entity'),
            'MAX(' . $positionField . ')'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        )->where(
            $connection->quoteIdentifier('level') . ' = :c_level'
        );

        $position = $connection->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }
        
        return $position;
    }
    
    public function getLookbookCategoryTable()
    {
        if (!$this->_lookbookCategoryTable) {
            $this->_lookbookCategoryTable = $this->getTable('cdzlookbook_category_lookbook');
        }
        return $this->_lookbookCategoryTable;
    }
    
    public function getLookbooksPosition($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getLookbookCategoryTable(),
            ['lookbook_id', 'position']
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }
    
    protected function _saveCategoryLookbooks($category)
    {
        $category->setIsChangedLookbookList(false);
        $id = $category->getId();
        /**
         * new category-lookbook relationships
         */
        $lookbooks = $category->getData('posted_lookbooks');
        
        /**
         * Example re-save category
         */
        if ($lookbooks === null) {
            return $this;
        }

        /**
         * old category-product relationships
         */
        $oldLookbooks = $category->getLookbooksPosition();
        

        $insert = array_diff_key($lookbooks, $oldLookbooks);
        $delete = array_diff_key($oldLookbooks, $lookbooks);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldLookbooks array)
         */
        $update = array_intersect_key($lookbooks, $oldLookbooks);
        // get all elements of array1 having key exiting in array 2
        
        $update = array_diff_assoc($update, $oldLookbooks);
        // compare 2 arrays and return different elements (both key and value)
        
        $connection = $this->getConnection();

        /**
         * Delete lookbooks from category
         */
        if (!empty($delete)) {
            $cond = ['lookbook_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getLookbookCategoryTable(), $cond);
        }

        /**
         * Add lookbooks to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $lookbookId => $position) {
                $data[] = [
                    'category_id'   => (int)$id,
                    'lookbook_id'   => (int)$lookbookId,
                    'position'      => (int)$position,
                ];
            }
            $connection->insertMultiple($this->getLookbookCategoryTable(), $data);
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $lookbookId => $position) {
                $delta = $position - $oldLookbooks[$lookbookId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $lookbookId;
            }

            foreach ($newPositions as $delta => $lookbookIds) {
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['category_id = ?' => (int)$id, 'lookbook_id IN (?)' => $lookbookIds];
                $connection->update($this->getLookbookCategoryTable(), $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $lookbookIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $category->setChangedLookbookIds($lookbookIds);
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedLookbookList(true);

            /**
             * Setting affected lookbooks to category for third party engine index refresh
             */
            $lookbookIds = array_keys($insert + $delete + $update);
            $category->setAffectedLookbookIds($lookbookIds);
        }
        return $this;
    }
    
    public function getParentCategories($category)
    {
        $pathIds = array_reverse(explode(',', $category->getPathInStore()));
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categories */
        $categories = $this->_categoryCollectionFactory->create();
        return $categories->setStore(
            $this->_storeManager->getStore()
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'url_key'
        )->addFieldToFilter(
            'entity_id',
            ['in' => $pathIds]
        )->addFieldToFilter(
            'is_active',
            1
        )->load()->getItems();
    }
    
    public function getChildrenCount($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable(),
            'children_count'
        )->where(
            'entity_id = :entity_id'
        );
        $bind = ['entity_id' => $categoryId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    public function changeParent(
        \Codazon\Lookbookpro\Model\LookbookCategory $category,
        \Codazon\Lookbookpro\Model\LookbookCategory $newParent,
        $afterCategoryId = null
    ) {

        $childrenCount = $this->getChildrenCount($category->getId()) + 1;
        $table = $this->getEntityTable();
        $connection = $this->getConnection();
        $levelFiled = $connection->quoteIdentifier('level');
        $pathField = $connection->quoteIdentifier('path');

        /**
         * Decrease children count for all old category parent categories
         */
        $connection->update(
            $table,
            ['children_count' => new \Zend_Db_Expr('children_count - ' . $childrenCount)],
            ['entity_id IN(?)' => $category->getParentIds()]
        );

        /**
         * Increase children count for new category parents
         */
        $connection->update(
            $table,
            ['children_count' => new \Zend_Db_Expr('children_count + ' . $childrenCount)],
            ['entity_id IN(?)' => $newParent->getPathIds()]
        );

        $position = $this->_processPositions($category, $newParent, $afterCategoryId);
        

        $newPath = sprintf('%s/%s', $newParent->getPath(), $category->getId());
        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $category->getLevel();

        /**
         * Update children nodes path
         */
        $connection->update(
            $table,
            [
                'path' => new \Zend_Db_Expr(
                    'REPLACE(' . $pathField . ',' . $connection->quote(
                        $category->getPath() . '/'
                    ) . ', ' . $connection->quote(
                        $newPath . '/'
                    ) . ')'
                ),
                'level' => new \Zend_Db_Expr($levelFiled . ' + ' . $levelDisposition)
            ],
            [$pathField . ' LIKE ?' => $category->getPath() . '/%']
        );
        /**
         * Update moved category data
         */
        $data = [
            'path' => $newPath,
            'level' => $newLevel,
            'position' => $position,
            'parent_id' => $newParent->getId(),
        ];

        $connection->update($table, $data, ['entity_id = ?' => $category->getId()]);

        // Update category object to new data
        $category->addData($data);
        $category->unsetData('path_ids');

        /* custom */
        $category->save();
        
        return $this;
    }
        
    protected function _processPositions($category, $newParent, $afterCategoryId)
    {
        $table = $this->getEntityTable();
        $connection = $this->getConnection();
        $positionField = $connection->quoteIdentifier('position');

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' - 1')];
        $where = [
            'parent_id = ?' => $category->getParentId(),
            $positionField . ' > ?' => $category->getPosition(),
        ];
        $connection->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterCategoryId) {
            $select = $connection->select()->from($table, 'position')->where('entity_id = :entity_id');
            $position = $connection->fetchOne($select, ['entity_id' => $afterCategoryId]);
            $position += 1;
        } else {
            $position = 1;
        }

        $bind = ['position' => new \Zend_Db_Expr($positionField . ' + 1')];
        $where = ['parent_id = ?' => $newParent->getId(), $positionField . ' >= ?' => $position];
        $connection->update($table, $bind, $where);

        return $position;
    }    

    public function getChildren($category, $recursive = true)
    {
        $linkField = $this->getLinkField();
        $attributeId = $this->getIsActiveAttributeId();
        $backendTable = $this->getTable([$this->getEntityTablePrefix(), 'int']);
        $connection = $this->getConnection();
        $checkSql = $connection->getCheckSql('c.value_id > 0', 'c.value', 'd.value');
        $bind = [
            'attribute_id' => $attributeId,
            'store_id' => $category->getStoreId(),
            'scope' => 1,
            'c_path' => $category->getPath() . '/%',
        ];
        $select = $this->getConnection()->select()->from(
            ['m' => $this->getEntityTable()],
            'entity_id'
        )->joinLeft(
            ['d' => $backendTable],
            "d.attribute_id = :attribute_id AND d.store_id = 0 AND d.{$linkField} = m.{$linkField}",
            []
        )->joinLeft(
            ['c' => $backendTable],
            "c.attribute_id = :attribute_id AND c.store_id = :store_id AND c.{$linkField} = m.{$linkField}",
            []
        )->where(
            $checkSql . ' = :scope'
        )->where(
            $connection->quoteIdentifier('path') . ' LIKE :c_path'
        );
        if (!$recursive) {
            $select->where($connection->quoteIdentifier('level') . ' <= :c_level');
            $bind['c_level'] = $category->getLevel() + 1;
        }

        return $connection->fetchCol($select, $bind);
    }
    
    protected function _proccessUrlPath($object)
    {
        $urlPath = $object->getData('url_key');
        $currentCatgeory = clone $object;
        while ($parentId = $currentCatgeory->getData('parent_id')) {
            $parent = $this->_categoryCollectionFactory->create()
                ->setStoreId($object->getStoreId())
                ->addAttributeToSelect([
                    'url_key'
                ])->addFieldToFilter(
                    'entity_id', $parentId
                )->getFirstItem();
            if ($parent->getData('level') <= 1) {
                break;
            }
            $urlPath = $parent->getUrlKey() . '/' . $urlPath;
            $currentCatgeory = $parent;
        }
        $i = 0;
        $currentPath = $urlPath;
        do {
            $exits = $this->_categoryCollectionFactory->create()
                ->setStoreId($object->getStoreId())
                ->addFieldToFilter(
                    'entity_id', ['neq' => $parentId]
                )->addAttributeToFilter(
                    'url_path', $currentPath . '.html'
                )->count();
            if ($exits) {
                $i++;
                $currentPath = $currentPath . '-' . $i;
            }
        } while ($exits);
        
        if ($i > 0) {
            $i = '-' . $i;
        } else {
            $i = '';
        }
        $urlPath = $urlPath . $i . '.html';
        $object->setData('url_path', $urlPath);
        return $this;
    }
    
    /**
     * Process category data before delete
     * update children count for parent category
     * delete child categories
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\DataObject $object)
    {
        parent::_beforeDelete($object);
        $this->processDelete($object);
        $this->deleteChildren($object);
    }
    
    public function processDelete($category)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Category $resourceModel */
        $resourceModel = $category->getResource();
        /**
         * Update children count for all parent categories
         */
        $parentIds = $category->getParentIds();
        if ($parentIds) {
            $childDecrease = $category->getChildrenCount() + 1;
            // +1 is itself
            $data = ['children_count' => new \Zend_Db_Expr('children_count - ' . $childDecrease)];
            $where = ['entity_id IN(?)' => $parentIds];
            $resourceModel->getConnection()->update($resourceModel->getEntityTable(), $data, $where);
        }
    }
    
    public function isForbiddenToDelete($categoryId)
    {
        if ($categoryId == \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
            return true;
        }
        
        $stores = $this->_storeManager->getStores();
        
        foreach ($stores as $store) {
            if ($categoryId == $store->getConfig('codazon_lookbook/general/root_category')) {
                return true;
            }
        }
        return false;
    }
    
    public function deleteChildren(\Magento\Framework\DataObject $object)
    {
        if ($object->getSkipDeleteChildren()) {
            return $this;
        }

        $categories = $this->_categoryCollectionFactory->create();
        $categories->addAttributeToFilter('path', ['like' => $object->getPath() . '/%']);
        $childrenIds = $categories->getAllIds();
        foreach ($categories as $category) {
            $category->setSkipDeleteChildren(true);
            $category->delete();
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);
        return $this;
    }
}
