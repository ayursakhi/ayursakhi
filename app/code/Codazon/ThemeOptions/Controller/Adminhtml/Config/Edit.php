<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Controller\Adminhtml\Config;
class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Config\Model\Config $backendConfig
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\ThemeOptions\Model\Config\Structure $configStructure,
        \Codazon\ThemeOptions\Model\Config $backendConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct($context);
        $this->_configStructure = $configStructure;
        $this->resultPageFactory = $resultPageFactory;
        $this->_objectManager = $context->getObjectManager();
        $this->registry = $registry;
        $this->_scopeConfig = $scopeConfig;
        $this->isDeveloperMode = (\Magento\Framework\App\State::MODE_DEVELOPER === $state->getMode() || \Magento\Framework\App\State::MODE_DEFAULT === $state->getMode());
    }
    
    public function getCurrentThemeId(){
    	$path = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        $this->session = 'design';
        $this->website = $this->getRequest()->getParam('website');
        $this->store = $this->getRequest()->getParam('store');
        $this->code = $this->getRequest()->getParam('code');
        
        if($this->store)
        {
            $this->currentThemeId = $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->store);
        }
        else if($this->website)
        {
        	$this->currentThemeId = $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES,$this->website);
        }
        else
        {
            $this->currentThemeId = $this->_scopeConfig->getValue($path,'default',0);
        }
        return $this->currentThemeId;
    }

    /**
     * Edit configuration section
     *
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        $current = $this->getRequest()->getParam('section');
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');
        $code = $this->getRequest()->getParam('code');
        $theme = $this->getRequest()->getParam('theme_id');
        
		$currentThemeId = $this->getCurrentThemeId();
		if($theme != $currentThemeId){
			$resultRedirect = $this->resultRedirectFactory->create();
		    return $resultRedirect->setPath(
		        'themeoptions/config/edit',
		        [
		        	'theme_id' => $currentThemeId,
		        	'code'	   => $code,
		        	'section'  => $current,
		            'website'  => $website,
		            'store'    => $store
		        ]
		    );
		}
        $section = $this->_configStructure->getElement($current);
        $resultPage = $this->resultPageFactory->create();

        if($theme){
		    $themeModel = $this->_objectManager->get('\Magento\Theme\Model\Theme');
		    $themeModel->load($theme);
		    if(strpos($themeModel->getThemePath(), 'Codazon')!==false){
			    $resultPage->getConfig()->getTitle()->prepend(__($themeModel->getThemeTitle()));		    
			    $layout = $resultPage->getLayout();
			    $layout->addBlock('Codazon\ThemeOptions\Block\Adminhtml\Config\Tabs','adminhtml.system.config.tabs','left');
                if($this->isDeveloperMode){
                    $layout->addBlock('Codazon\ThemeOptions\Block\Adminhtml\Config\Edit','adminhtml.system.config.edit','content');
                }else{
                    $block = $this->_objectManager->create('Magento\Framework\View\Element\Text');
                    $block->setText(__('Please change to developer mode to use this function.'));
                    $layout->addBlock($block,'adminhtml.system.config.edit','content');
                }
			}
        }
        
        $resultPage->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo([$current]);
        $resultPage->addBreadcrumb(__('System'), __('System'), $this->getUrl('*\/system'));
        
        return $resultPage;
    }
}
