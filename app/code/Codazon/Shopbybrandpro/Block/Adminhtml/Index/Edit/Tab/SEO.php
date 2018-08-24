<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Block\Adminhtml\Index\Edit\Tab;
	
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Theme\Helper\Storage;

class SEO extends Generic implements TabInterface
{
    
	public function getTabLabel()
	{
		return __('Search Engine Optimization');
	}
    
	public function getTabTitle()
	{
		return __('Search Engine Optimization');
	}
    
	public function canShowTab()
	{
		return true;
	}
    
	public function isHidden()
	{
		return false;
	}
    
	protected function _prepareForm()
	{
		$model = $this->_coreRegistry->registry('brand');
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('brand_');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('Search Engine Optimization'), 'class' => 'fieldset-wide']
		);
        
        $fieldset->addField(
			'brand_url_key',
			'text',
			['name' => 'brand_url_key', 'label' => __('URL Key'), 'title' => __('URL Key'), 'required' => false]
		);
        
        $fieldset->addField(
			'brand_meta_title',
			'text',
			['name' => 'brand_meta_title', 'label' => __('Meta Title'), 'title' => __('Meta Title'), 'required' => false]
		);
        
        $fieldset->addField(
			'brand_meta_description',
			'textarea',
			['name' => 'brand_meta_description', 'label' => __('Meta Description'), 'title' => __('Meta Description'), 'required' => false]
		);
        
        $fieldset->addField(
			'brand_meta_keyword',
			'text',
			['name' => 'brand_meta_keyword', 'label' => __('Meta Keyword'), 'title' => __('Meta Keyword'), 'required' => false]
		);

		$form->setDataObject($model);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}

}