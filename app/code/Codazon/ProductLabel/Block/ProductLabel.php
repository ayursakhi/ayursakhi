<?php
namespace Codazon\ProductLabel\Block;
class ProductLabel extends \Magento\Framework\View\Element\Template{
	public $objectManager;
    protected $_template = 'productlabel.phtml';
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = [])
    {
		$this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }
	public function addObject($_object)
    {
        $this->setData('object',$_object);
        return $this;
    }
	public function getObject()
    {
        return $this->getData('object');
    }
}