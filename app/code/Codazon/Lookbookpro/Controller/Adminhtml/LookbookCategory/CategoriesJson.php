<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookCategory;

use Magento\Backend\App\Action;

class CategoriesJson extends AbstractLookbookCategory
{
    protected $resultJsonFactory;
    protected $layoutFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
    }
    
    public function execute()
    {
        if ($this->getRequest()->getParam('expand_all')) {
            $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->setIsTreeWasExpanded(true);
        } else {
            $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->setIsTreeWasExpanded(false);
        }
        $categoryId = (int)$this->getRequest()->getPost('id');
        if ($categoryId) {
            $this->getRequest()->setParam('id', $categoryId);

            $category = $this->_initCategory();
            if (!$category) {
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('lookbookpro/*/', ['_current' => true, 'id' => null]);
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setJsonData(
                $this->layoutFactory->create()->createBlock(\Codazon\Lookbookpro\Block\Adminhtml\LookbookCategory\Tree::class)
                    ->getTreeJson($category)
            );
        }
    }
}