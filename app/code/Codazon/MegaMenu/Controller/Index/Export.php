<?php
namespace Codazon\MegaMenu\Controller\Index;
class Export extends \Magento\Framework\App\Action\Action
{
	protected $resultForwardFactory;
	protected $menuFactory;
	protected $csv;
	protected $fixtureManager;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
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
		$header = [];
		$i = 0;
		foreach($collection->getItems() as $item){
			$item->unsetData('menu_id');
			$data[] = $item->getData();
			if($i == 0){
				foreach($item->getData() as $key => $value){
					$header[] = $key;
				}
			}
			$message[] = '<p><strong>'.$item->getData('title').'</strong> (identifier: <em>'.$item->getData('identifier').'</em>)</p>';
			$i++;
		}
		$exportData = array_merge(array($header),$data);
		$file = $this->fixtureManager->getFixture('Codazon_MegaMenu::fixtures/codazon_megamenu.csv');
		$this->csv->saveData($file, $exportData);
		$this->getResponse()->setBody('<p>Export successfully.</p>'.implode('',$message));
    }
}