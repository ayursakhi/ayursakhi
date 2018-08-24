<?php
namespace Codazon\MegaMenu\Controller\Adminhtml\Index;
class Export extends \Magento\Backend\App\Action
{
	protected $resultForwardFactory;
	protected $menuFactory;
	protected $csv;
	protected $fixtureManager;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\Setup\SampleData\FixtureManager $fixtureManager,
		\Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
		\Magento\Framework\File\Csv	$csv,
		\Codazon\MegaMenu\Model\MegamenuFactory $menuFactory
	)
	{
		$this->resultForwardFactory = $resultForwardFactory;
		$this->menuFactory = $menuFactory;
		$this->csv = $csv;
		$this->fixtureManager = $fixtureManager;
		parent::__construct($context);
	}
	/**
     * Is the user allowed to view the menu grid.
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Codazon_MegaMenu::save');
    }
	public function execute()
    {
		$menu = $this->menuFactory->create();
		$collection = $menu->getCollection();
		$data = [];
		$message = [];
		foreach($collection->getItems() as $item){
			$item->unsetData('menu_id');
			$data[] = $item->getData();
			$message[] = '<p>'.$item->getData('identifier').'</p>';
		}
		
		$file = $this->fixtureManager->getFixture('Codazon_MegaMenu::fixtures/codazon_megamenu.csv');
		$this->csv->saveData($file, $data);
		$this->getReponse()->setBody('<p>Export successfully:</p>'.implode('',$message));
    }
}