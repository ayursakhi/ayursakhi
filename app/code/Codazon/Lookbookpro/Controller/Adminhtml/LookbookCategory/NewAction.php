<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookCategory;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;

class NewAction extends AbstractLookbookCategory
{
	protected $resultPageFactory;
    
    protected $_coreRegistry;
    
	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}
	
    /**
     * Is the user allowed to view the menu grid.
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_category_edit');
    }
    
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_Lookbookpro::cdzlookbook_category');
		return $resultPage;
	}
    
	public function execute()
	{
		
		$model = $this->_objectManager->create($this->modelClass);
		$storeId = $this->getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $model->setData('store_id', $storeId);
		
        $parentId = (int)$this->getRequest()->getParam('parent');
        if (!$parentId) {
            $parentId = \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
            $this->getRequest()->setParam('parent', $parentId);
        }
        
        if ($parentId) {
            $model->setData('parent_id', $parentId);
            $model->setData('parent', $parentId);
        }
	
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
	
		$this->_coreRegistry->register('lookbookpro_cdzlookbook_category', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			__('New Lookbook Category'),
			__('New Lookbook Category')
		);
		
        $resultPage->getConfig()->getTitle()->prepend(__('Lookbook Category'));
		$resultPage->getConfig()->getTitle()
			->prepend(__('New Lookbook Category'));
        
        $resultPage->getLayout()->unsetElement('store_switcher');
        return $resultPage;
	}
}

