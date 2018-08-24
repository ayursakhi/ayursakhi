<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Block;
class Instagramphotos extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	protected $_template = null;
	const API_URL = 'https://api.instagram.com/v1/users/self/media/recent';
	const ACCESS_TOKEN = '3893338542.38fb276.e8dbfaac57214bf69c0439027ee39d85';
	
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);     
        $this->httpContext = $httpContext;     
        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => ['CDZ_INSTAGRAM',
        ], ]);        
    }
	public function getCacheKeyInfo()
    {
        $instagram = serialize($this->getData());
        return [
            'CDZ_INSTAGRAM',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),                    
            $instagram
        ];
    }
	public function fetchData($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 20);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
  	}
	
	public function getInstagramRecentPhotos(){
		$accessToken = $this->getData('access_token')?$this->getData('access_token'):self::ACCESS_TOKEN;
		$url = self::API_URL . "?access_token={$accessToken}";
		$result = json_decode($this->fetchData($url));
		if(isset($result->data))
		{
			return $result->data;
		}
	}
	
	public function getTemplate()
    {   
        if($this->_template == null){
			if($this->getData('custom_template')){
				$this->_template = $this->getData('custom_template');
			}else{
				$this->_template = 'Codazon_ThemeOptions::widget/instagramphotos/grid.phtml';
			}
		}
		return $this->_template;
    }
	
}