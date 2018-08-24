<?php
namespace Codazon\Shopbybrandpro\Model;
use Codazon\ProductLabel\Api\Data\ProductLabelInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;

class SelectedBrands extends \Magento\Framework\Model\AbstractModel
{
	protected function _construct()
	{
		$this->_init('Codazon\Shopbybrandpro\Model\ResourceModel\SelectedBrands');
	}
	
}