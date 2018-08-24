<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Controller\Adminhtml\Theme;

class Save extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Codazon\ThemeOptions\Framework\App\ConfigFactory $themeConfigFactory,
        \Magento\Config\Model\ConfigFactory $configFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    )
    {
        parent::__construct($context);
        $this->_authorization = $context->getAuthorization();
        $this->_auth = $context->getAuth();
        $this->_helper = $context->getHelper();
        $this->_backendUrl = $context->getBackendUrl();
        $this->_formKeyValidator = $context->getFormKeyValidator();
        $this->_localeResolver = $context->getLocaleResolver();
        $this->_canUseBaseUrl = $context->getCanUseBaseUrl();
        $this->_session = $context->getSession();
        $this->_themeConfigFactory = $themeConfigFactory;
        $this->_configFactory = $configFactory;
        $this->indexerRegistry = $indexerRegistry;
    }
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $params = array();
        $themeConfigOb = $this->_themeConfigFactory->create();
        $path = \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID;
        $config = $this->_configFactory->create();
        $this->theme_id = $this->getRequest()->getParam('theme_id');
        $this->section = 'general_section';
        $this->website = $this->getRequest()->getParam('website');
        $this->store = $this->getRequest()->getParam('store');
        $this->code = $this->getRequest()->getParam('code');
        
        $params['code'] = $this->code;
        
        if($this->website)
        {
            $params['website'] = $this->website;
        }
        else if($this->store)
        {
            $params['store'] = $this->store;
        }
        $config->setData($params);
        $config->setDataByPath($path,$this->theme_id);
        $config->save();
        
        $cmsHomePage = $themeConfigOb->getValue('cms_home_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORES,$this->store);
        $config->setDataByPath('web/default/cms_home_page',$cmsHomePage);
        $config->save();
        $this->indexerRegistry->get(\Magento\Theme\Model\Data\Design\Config::DESIGN_CONFIG_GRID_INDEXER_ID)->reindexAll();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath(
            'themeoptions/config/edit',
            [
                'theme_id' => $this->theme_id,
                'code'     => $this->code,
                'section'  => $this->section,
                'website'  => $this->website,
                'store'    => $this->store
            ]
        );
    }
}
