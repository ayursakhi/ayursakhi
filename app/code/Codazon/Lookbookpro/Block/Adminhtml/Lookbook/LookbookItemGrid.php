<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Block\Adminhtml\Lookbook;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class LookbookItemGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\itemFactory
     */
    protected $_itemFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\LookbookItemFactory $itemFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Codazon\Lookbookpro\Model\LookbookItemFactory $itemFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_category_products');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getLookbook()
    {
        return $this->_coreRegistry->registry('lookbookpro_cdzlookbook');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $itemIds = $this->_getSelectedItems();
            if (empty($itemIds)) {
                $itemIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $itemIds]);
            } elseif (!empty($itemIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $itemIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getLookbook()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }
        $collection = $this->_itemFactory->create()->getCollection()->addAttributeToSelect(
            'name'
        )->joinField(
            'position',
            'cdzlookbook_item_group',
            'position',
            'item_id=entity_id',
            'at_position.lookbook_id=' . (int)$this->getRequest()->getParam('entity_id', 0),
            'left'
        );
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        if ($storeId > 0) {
            $collection->setStoreId($storeId);
        }
        $this->setCollection($collection);

        if ($this->getLookbook()->getLookbookItemsReadonly()) {
            $itemIds = $this->_getSelectedItems();
            if (empty($itemIds)) {
                $itemIds = 0;
            }
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $itemIds]);
        }

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        if (!$this->getLookbook()->getLookbookItemsReadonly()) {
            $this->addColumn(
                'in_category',
                [
                    'type' => 'checkbox',
                    'name' => 'in_category',
                    'values' => $this->_getSelectedItems(),
                    'index' => 'entity_id',
                    'header_css_class' => 'col-select col-massaction',
                    'column_css_class' => 'col-select col-massaction'
                ]
            );
        }
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn('name', ['header' => __('Name'), 'index' => 'name']);
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => !$this->getLookbook()->getLookbookItemsReadonly()
            ]
        );
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => !$this->getLookbook()->getLookbookItemsReadonly()
            ]
        );
        
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'width'     => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions'   => [
                    [
                        'caption' => __('Edit'),
                        'url'     => array(
                            'base' => 'lookbookpro/lookbookitem/edit',
                        ),
                        'target' => '_blank',
                        'field'   => 'entity_id'
                    ]
                ],
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('lookbookpro/lookbook/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedItems()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getLookbook()->getItemsPosition();
            return array_keys($products);
        }
        return $products;
    }
}
