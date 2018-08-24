<?php
/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\MegaMenu\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;


class MenuData extends \Magento\Framework\Model\AbstractModel
{
	/**
     * @var \Magento\Framework\Setup\SampleData\FixtureManager
     */
	private $fixtureManager;
	/**
     * @var \Magento\Framework\File\Csv
     */
	protected $csvReader;
	
	 /**
     * @var \Codazon\MegaMenu\Model\MegamenuFactory
     */
	protected $menuFactory;
	
	public function __construct(
        SampleDataContext $sampleDataContext,
        \Codazon\MegaMenu\Model\MegamenuFactory $menuFactory
    ) {
        $this->fixtureManager = $sampleDataContext->getFixtureManager();
        $this->csvReader = $sampleDataContext->getCsvReader();
        $this->menuFactory = $menuFactory;
    }
	
	public function install(array $fixtures)
    {
		foreach ($fixtures as $fileName) {
			$fileName = $this->fixtureManager->getFixture($fileName);
			if (!file_exists($fileName)) {
                continue;
            }
			
			$rows = $this->csvReader->getData($fileName);
			$header = array_shift($rows);
			foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $menu = $this->saveMenu($data);
                $menu->unsetData();
            }
		}
	}
	
	protected function saveMenu($data)
    {
		$menu = $this->menuFactory->create();
		$menu->getResource()->load($menu, $data['identifier']);
		if (!$menu->getData()) {
            $menu->setData($data);
        } else {
            $menu->addData($data);
        }
		$menu->setIsActive(1);
		$menu->save();
		return $menu;
	}
}