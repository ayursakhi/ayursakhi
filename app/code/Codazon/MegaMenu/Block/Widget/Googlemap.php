<?php
namespace Codazon\MegaMenu\Block\Widget;
use Magento\Framework\View\Element\Template;
class Googlemap extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	public function getCacheKeyInfo()
    {
        return [
            'GOOGLEMAP',
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
            'cache_tags' => ['GOOGLEMAP']
		]);
	}
	public function getIdentities()
    {
        return [\Codazon\MegaMenu\Model\Megamenu::CACHE_TAG . '_' . md5(json_encode($this->getData()))];
    }
	public function getTemplate()
    {
        return 'googlemap.phtml';
    }
	public function getGeocodeByAddress($address){
		return json_decode(file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.urlencode($address).'&sensor=false'));
	}
	public function getGoogleMapJavascriptUrl(){
		return "//maps.googleapis.com/maps/api/js?v=3.25&signed_in=true&key=AIzaSyByF5Th99QzkJtIhod9awRaDK2CGSNB43o";
	}
	public function getImageUrl($path){
		return $this->_assetRepo->getUrl('Codazon_MegaMenu/images/'.$path);
	}
}