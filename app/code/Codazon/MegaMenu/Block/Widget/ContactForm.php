<?php
namespace Codazon\MegaMenu\Block\Widget;
use Magento\Framework\View\Element\Template;
class ContactForm extends \Magento\Contact\Block\ContactForm implements \Magento\Widget\Block\BlockInterface
{
	public function getCacheKeyInfo()
    {
        return [
            'CONTACTFORM',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            md5(json_encode($this->getData()))
        ];
    }
	public function __construct(
		Template\Context $context,
        array $data = []
	){
		parent::__construct($context, $data);
		$this->_assetRepo = $context->getAssetRepository();
		$this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['CONTACTFORM']
		]);
	}
	public function getIdentities()
    {
        return [\Codazon\MegaMenu\Model\Megamenu::CACHE_TAG . '_' . md5(json_encode($this->getData()))];
    }
	public function getTemplate()
    {
        return 'contact-form.phtml';
    }
}