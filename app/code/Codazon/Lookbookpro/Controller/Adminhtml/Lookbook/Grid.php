<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\Lookbook;

use Magento\Backend\App\Action;

class Grid extends AbstractLookbook
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
		\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry
	) {
		$this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->_coreRegistry = $registry;
		parent::__construct($context);
	}
    
	public function execute()
	{
		$id = $this->getRequest()->getParam($this->primary);
		$model = $this->_objectManager->create($this->modelClass);
		
        $storeId = $this->getRequest()->getParam('store', \Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $model->setData('store_id', $storeId);
		
        if ($id) {
			$model->load($id);
		}
        $this->_coreRegistry->register('lookbookpro_cdzlookbook', $model);
        if (!$model) {
            
        }
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \Codazon\Lookbookpro\Block\Adminhtml\Lookbook\LookbookItemGrid::class,
                'category.product.grid'
            )->toHtml()
        );
	}
}
