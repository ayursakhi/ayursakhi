<?php
namespace Codazon\QuickShop\Block\Product\Renderer;
class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
	const SWATCH_RENDERER_TEMPLATE = 'Codazon_QuickShop::product/view/renderer.phtml';
	const CONFIGURABLE_RENDERER_TEMPLATE = 'Codazon_QuickShop::product/view/type/options/configurable.phtml';
    const MEDIA_CALLBACK_ACTION = 'quickview/ajax/media';
    
	protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
            self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }
    protected function isProductHasSwatchAttribute()
    {
        if (isset($this->isProductHasSwatchAttribute)){
            return $this->isProductHasSwatchAttribute;
        }else{
            return parent::isProductHasSwatchAttribute();
        }
    }
    
    public function getMediaCallback()
    {
        return $this->getUrl(self::MEDIA_CALLBACK_ACTION, ['_secure' => $this->getRequest()->isSecure()]);
    }
    
}