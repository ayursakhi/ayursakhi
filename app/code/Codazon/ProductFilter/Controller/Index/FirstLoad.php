<?php
/**
 * Product controller.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ProductFilter\Controller\Index;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Model\Product as ModelProduct;

class FirstLoad extends \Magento\Framework\App\Action\Action
{
   	const PAGE_VAR_NAME = 'np';
	protected $resultPageFactory;
	protected $productsListBlock;
	
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Codazon\ProductFilter\Block\Product\FirstLoad $productsListBlock,
		\Magento\Framework\App\CacheInterface $cache,
		\Magento\Framework\App\Cache\StateInterface $cacheStage
    ) {
		$this->resultPageFactory = $resultPageFactory;
        	$this->productsListBlock = $productsListBlock;
        $this->_cache = $cache;
        $this->_cacheStage = $cacheStage;
		parent::__construct($context);	
    }

    public function getCacheKeyInfo()
    {
        return $this->getRequest()->getPostValue();
    }

    public function insertCurrentDate($html){
    	return str_replace("{{current_date}}",date("M d, Y G:i:s"),$html);
    }
   
    public function execute()
    {
    	//ob_start('ob_gzhandler');
        $this->getRequest()->setRequestUri('/');
		$this->getResponse()->setHeader('Content-type','application/json');
		if(!$this->getRequest()->getParams()){
			die('empty');
		}
		$page = $this->resultPageFactory->create(false, ['isIsolated' => true]);

		$key = md5(serialize($this->getCacheKeyInfo()));
		
		if($this->_cacheStage->isEnabled('block_html')){
			$html = $this->_cache->load($key);
			if($html == ""){
				$html = $page->getLayout()->getBlock('product_ajax_load')->toHtml();
				$this->_cache->save($html,$key,['block_html'],null);
			}
		}else{
			$html = $page->getLayout()->getBlock('product_ajax_load')->toHtml();
		}

		$html = $this->insertCurrentDate($html);
		

		$this->getResponse()->setBody($html);
	}
}