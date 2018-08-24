<?php
/**
 * Copyright © 2016 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Codazon\MegaMenu\Setup;
use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var Setup\SampleData\Executor
     */
	private $menu;
	
	public function __construct(
        \Codazon\MegaMenu\Model\MenuData $menu
    ) {
        $this->menu = $menu;
    }
	/**
     * {@inheritdoc}
     */
    public function install()
    {
		$this->menu->install([
			'Codazon_MegaMenu::fixtures/codazon_megamenu.csv'
		]);
	}
}