<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;

class Edit extends AbstractLookbook
{
	/**
	* Core registry
	*
	* @var \Magento\Framework\Registry
	*/
	protected $_coreRegistry = null;
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	
	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $registry
	) {
		$this->resultPageFactory = $resultPageFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}
    
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_edit');
    }
    
	protected function _initAction()
	{
		$resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codazon_Lookbookpro::cdzlookbook');
		return $resultPage;
	}
    
	public function execute()
	{
		$id = $this->getRequest()->getParam($this->primary);
		$model = $this->_objectManager->create($this->modelClass);
		
        $storeId = $this->getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $model->setData('store_id', $storeId);
		
        
        if ($id) {
			$model->load($id);
            
			if (!$model->getId()) {
				$this->messageManager->addError(__('This Lookbook no longer exists.'));
				/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
            
            $categoriesPosition = $model->getCategoryIds();
            
		}
        
		$data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		$this->_coreRegistry->register('lookbookpro_cdzlookbook', $model);
	
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
		$resultPage->addBreadcrumb(
			$id ? __('Edit Lookbook') : __('New Lookbook'),
			$id ? __('Edit Lookbook') : __('New Lookbook')
		);
		$resultPage->getConfig()->getTitle()->prepend(__('Lookbook'));
        
		$resultPage->getConfig()->getTitle()
			->prepend($model->getId() ? $model->getData('name') : __('New Lookbook'));	
		return $resultPage;
	}
}

