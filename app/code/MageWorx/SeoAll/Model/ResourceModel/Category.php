<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoAll\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryInterface;

/**
 * This class was created for avoid magento bug:
 * @see https://github.com/magento/magento2/issues/6076
 */
class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    /**
     * @var \MageWorx\SeoAll\Helper\LinkFieldResolver
     */
    protected $linkFieldResolver;

    /**
     * Category constructor.
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \MageWorx\SeoAll\Helper\LinkFieldResolver $linkFieldResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageWorx\SeoAll\Helper\LinkFieldResolver $linkFieldResolver
    ) {
        parent::__construct($context, $storeManager, $modelFactory, $eventManager, $categoryTreeFactory, $categoryCollectionFactory);
        $this->linkFieldResolver = $linkFieldResolver;
    }

    /**
     * Avoid magento bug related to getRawAttributeValue()
     *
     * @see https://github.com/magento/magento2/issues/6076
     * @param string $alias
     * @return string
     */
    public function getTable($alias)
    {
        if ($alias == 'catalog_product_entity') {
            $alias = $this->getEntityTable();
        }
        return parent::getTable($alias);
    }

    /**
     * @return string
     */
    public function getLinkField()
    {
        return $this->linkFieldResolver->getLinkField(CategoryInterface::class, 'entity_id');
    }

    /**
     * @param $entityId
     * @return string
     */
    public function resolveEntityId($entityId) {

        if ($this->getEntityIdField() == $this->getLinkField()) {
            return $entityId;
        }

        $select    = $this->getConnection()->select();
        $tableName = $this->getTable('catalog_category_entity');

        $select->from($tableName, [$this->getLinkField()])
               ->where('entity_id = ?', $entityId);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Save attribute
     *
     * Fixed Magento bug: The values are deleted from all the stores while saving the attribute with the empty value.
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $attributeCode
     * @return $this|\Magento\Catalog\Model\ResourceModel\Category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAttribute(\Magento\Framework\DataObject $object, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $backend   = $attribute->getBackend();
        $table     = $backend->getTable();
        $entity    = $attribute->getEntity();

        $newValue = $object->getData($attributeCode);

        if ($attribute->isValueEmpty($newValue)) {
            $newValue = null;
        }

        $connection = $this->getConnection();
        $row        = $this->getAttributeRow($entity, $object, $attribute);
        $conditions = [];

        foreach ($row as $field => $value) {
            $conditions[] = $connection->quoteInto($field . '=?', $value);
        }

        $where = implode(' AND ', $conditions);

        $connection->beginTransaction();

        try {
            $select = $connection->select()->from($table, 'value_id')->where($where);
            $origValueId = $connection->fetchOne($select);

            if ($origValueId !== false && $newValue === null) {
                $where = $where . ' AND ' . $connection->quoteInto('store_id=?', $object->getStoreId());
                $connection->delete($table, $where);
            } elseif ($origValueId !== false && $newValue !== null) {
                $this->_updateAttribute($object, $attribute, $origValueId, $newValue);
            } elseif ($origValueId === false && $newValue !== null) {
                $this->_insertAttribute($object, $attribute, $newValue);
            }

            $this->_processAttributeValues();
            $connection->commit();

        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @see https://github.com/magento/magento2/issues/15248
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param mixed $value
     * @return $this|\Magento\Catalog\Model\ResourceModel\Category
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $storeId    = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        $tableName  = $attribute->getBackend()->getTable();
        $connection = $this->getConnection();

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        $entityIdField = $this->getLinkField();

        if ($this->_storeManager->isSingleStoreMode()) {
            $storeId = $this->getDefaultStoreId();
            $connection->delete(
                $tableName,
                [
                    'store_id <> ?' => $storeId,
                    'attribute_id = ?' => $attribute->getAttributeId(),
                    "{$entityIdField} = ?" => $object->getData($entityIdField)
                ]
            );
        }

        $data = new \Magento\Framework\DataObject(
            [
                'store_id' => $storeId,
                'attribute_id' => $attribute->getAttributeId(),
                $entityIdField => $object->getData($entityIdField),
                'value' => $this->_prepareValueForSave($value, $attribute)
            ]
        );
        $bind = $this->_prepareDataForTable($data, $tableName);

        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$tableName][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$tableName][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$tableName][] = $bind;
        }

        return $this;
    }
}
