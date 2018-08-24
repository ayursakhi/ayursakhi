<?php
namespace Codazon\MegaMenu\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
	protected $resultLayoutFactory;
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
		$this->resultLayoutFactory = $resultLayoutFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		parent::__construct($context);
		
    }
	
    public function execute()
    {	
		if($this->getRequest()->getMethod() == 'POST'){
			$data = $this->getRequest()->getParams();
			$style = array();
			$stylesVars = array('css_class','dropdown_animation','dropdown_style');
			foreach($stylesVars as $stylesVar){
				if(isset($data[$stylesVar])){
					$style[$stylesVar] = $data[$stylesVar];
				}
			}
			$data['menu_id'] = 0;
			$data['style'] = json_encode($style);
			$resultLayout = $this->resultLayoutFactory->create();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$megamenu = $objectManager->create("Codazon\MegaMenu\Model\Megamenu");
			$megamenu->addData($data);
			$block = $objectManager->create('Codazon\MegaMenu\Block\Widget\Megamenu')
				->setCacheLifetime(null)
				->setMenuObject($megamenu);
			$this->getResponse()->setBody($block->toHtml());
		}else{
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('/');
			return $resultRedirect;
		}
	}
}