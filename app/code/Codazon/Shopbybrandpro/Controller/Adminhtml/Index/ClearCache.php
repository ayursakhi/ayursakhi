<?php
/**
 * Copyright © 2017 Codazon, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\Shopbybrandpro\Controller\Adminhtml\Index;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Exception\LocalizedException;

class ClearCache extends \Magento\Backend\App\Action
{
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Shopbybrandpro::save');
    }
    
    public function execute()
    {
        $imageHelper = $this->_objectManager->create('Codazon\Shopbybrandpro\Helper\Image');
        $resultRedirect = $this->resultRedirectFactory->create();
        
        try {
            $imageHelper->clearCache();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addException($e, __('Something went wrong while clearing image cache.'));
        }
        
        return $resultRedirect->setPath('*/*/');
        
    }
}