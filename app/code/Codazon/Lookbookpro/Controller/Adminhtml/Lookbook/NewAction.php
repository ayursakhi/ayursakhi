<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;

class NewAction extends AbstractLookbook
{
	protected $resultForwardFactory;
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory)
	{
		$this->resultForwardFactory = $resultForwardFactory;
		parent::__construct($context);
	}
	
    /**
     * Is the user allowed to view the menu grid.
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_Lookbookpro::cdzlookbook_edit');
    }
    
	public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}

