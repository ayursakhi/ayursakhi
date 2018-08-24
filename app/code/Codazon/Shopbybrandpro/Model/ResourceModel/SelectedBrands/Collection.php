<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Model\ResourceModel\SelectedBrands;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
	protected function _construct()
    {
		parent::_construct();

    }
	protected function _beforeLoad()
    {
        parent::_beforeLoad();
		
		$configAttributeCode = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('codazon_shopbybrand/general/attribute_code');
        
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        
        $optionValueTable = $this->getConnection()->select()
            ->from($this->getTable('eav_attribute_option_value'), ['oid' => 'option_id', 'store_id', 'brand_label' => 'value'])
            ->where("store_id = {$storeId}");
        
		$this->getSelect()
			->joinLeft(array('cea' => $this->getTable('catalog_eav_attribute') ),'main_table.attribute_id = cea.attribute_id','is_visible')
			->joinLeft(array('ea' => $this->getTable('eav_attribute') ),'cea.attribute_id = ea.attribute_id','attribute_code')
			->joinLeft(array('eaov' => $optionValueTable ), 'eaov.oid = main_table.option_id', ['brand_label'])
			->where("ea.attribute_code = '{$configAttributeCode}'")
			->group("main_table.option_id");
    }
}
