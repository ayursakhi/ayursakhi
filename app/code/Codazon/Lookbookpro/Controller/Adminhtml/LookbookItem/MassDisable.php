<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookItem;

use Magento\Backend\App\Action;
use Magento\Store\Model\Store;

class MassDisable extends \Codazon\Lookbookpro\Controller\Adminhtml\AbstractMassStatus
{
    protected $primary = 'entity_id';
    protected $collectionClass = 'Codazon\Lookbookpro\Model\ResourceModel\LookbookItem\Collection';
    protected $modelClass = 'Codazon\Lookbookpro\Model\LookbookItem';
    protected $status = 0;
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_save');
    }
    
    protected function setSuccessMessage($count)
    {
		$this->messageManager->addSuccess(__('A total of %1 record(s) have been disabled.', $count));
        return $this;
	}
}
