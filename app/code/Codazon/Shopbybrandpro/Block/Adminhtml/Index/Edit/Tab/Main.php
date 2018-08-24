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

class Main extends Generic implements TabInterface
{
    
	public function getTabLabel()
	{
		return __('Brand Information');
	}
    
	public function getTabTitle()
	{
		return __('Brand Information');
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
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\Config\ScopeConfigInterface');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('General Information'), 'class' => 'fieldset-wide']
		);
		if ($model->getEntityId()) {
			$fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
		}else{
			$model->addData([
				'is_active' => 1,
				'brand_is_featured' => 1 //$scopeConfig->getValue('codazon_shopbybrand/featured_brands/brand_is_featured_by_default')
			]);
		}
		
		$fieldset->addField('store', 'hidden', ['name' => 'store']);
		
		if ($model->getOptionId()) {
			$fieldset->addField('option_id', 'hidden', ['name' => 'option_id']);
		}
		$fieldset->addField(
			'brand_label',
			'label',
			['name' => 'brand_label', 'label' => __('Brand Label'), 'title' => __('Brand Label')]
		);
		
        // $fieldset->addField(
			// 'identifier',
			// 'text',
			// ['name' => 'identifier', 'label' => __('Indentifier'), 'title' => __('Indentifier'), 'required' => true]
		// );
        
		$fieldset->addField(
			'is_active',
			'select',
			['name' => 'is_active', 'label' => __('Active'), 'title' => __('Active'),
				'required' => true,
				'options' => ['1' => __('Yes'), '0' => __('No')]
			]
		);
		$fieldset->addField(
			'brand_description',
			'editor',
			['name' => 'brand_description', 'config' => $this->getWysiwygConfig(), 'label' => __('Description'), 'title' => __('Description'), 'required' => false]
		);
		// $fieldset->addField(
			// 'brand_content',
			// 'editor',
			// ['name' => 'brand_content', 'config' => $this->getWysiwygConfig(), 'label' => __('Content'), 'title' => __('Content'), 'required' => false]
		// );
		$renderer = $this->getLayout()->createBlock(
			'Codazon\Shopbybrandpro\Block\Adminhtml\Shopbybrandpro\AbstractHtmlField\Image'
		);
		
		$field = $fieldset->addField(
			'brand_thumbnail',
			'hidden',
			['name' => 'brand_thumbnail', 'label' => __('Thumbnail Image'), 'title' => __('Thumbnail Image'), 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
		);
		$field->setRenderer($renderer);
		$field = $fieldset->addField(
			'brand_cover',
			'hidden',
			['name' => 'brand_cover', 'label' => __('Cover Image'), 'title' => __('Cover Image'), 'required' => false, 'class' => 'input-image', 'onchange' => 'changePreviewImage(this)']
		);
		$field->setRenderer($renderer);		
	
		$fieldset->addField(
			'brand_is_featured',
			'select',
			['name' => 'brand_is_featured', 'label' => __('Is Featured'), 'title' => __('Is Featured'),
				'required' => true,
				'options' => ['1' => __('Yes'), '0' => __('No')]
			]
		);

		$form->setDataObject($model);
		$form->setValues($model->getData());
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
    
	public function getWysiwygConfig()
	{
		$config = new \Magento\Framework\DataObject();
		$config->setData([
			'enabled' => true,
			'hidden' => false,
			'popup_css' => $this->_assetRepo->getUrl(
				'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
			),
			'content_css' => $this->_assetRepo->getUrl(
				'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
			),
		]);
		return $config;	
	}
}