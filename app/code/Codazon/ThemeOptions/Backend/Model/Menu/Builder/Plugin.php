<?php
namespace Codazon\ThemeOptions\Backend\Model\Menu\Builder;
class Plugin
{
    public function __construct(
        \Magento\Backend\Model\Menu\Item\Factory $menuItemFactory,
        \Magento\Config\Model\ConfigFactory $configFactory,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->_itemFactory = $menuItemFactory;
        $this->_config = $configFactory->create();
        $this->_storeManager = $storeManager;
    }
    
    public function getThemeId()
    {
        $path = 'design/theme/theme_id';
        /** @var $section \Magento\Config\Model\Config\Structure\Element\Section */
        //$config = $this->_objectManager->create('Magento\Config\Model\Config');
        $this->session = 'design';
        $this->website = '';
        $this->store = '';
        if($this->_storeManager->isSingleStoreMode()){
            $websites = $this->_storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $this->website = $singleStoreWebsite->getId();
        }
        $this->code = '';
        $this->_config->setData([
            'session'   => $this->session,
            'website'   => $this->website,
            'store'     => $this->store
        ]);

        $this->currentThemeId = $this->_config->getConfigDataValue($path);
        return $this->currentThemeId;
    }
    
    public function afterGetResult($subject, $menu)
    {   
        $path = dirname(dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))).'/design/frontend/Codazon';
        $dirs = glob($path . '/*' , GLOB_ONLYDIR);
        
        $params = [];
        $addition = '';
        if($this->_storeManager->isSingleStoreMode()){
            $websites = $this->_storeManager->getWebsites();
            $singleStoreWebsite = array_shift($websites);
            $addition = '/website/'.$singleStoreWebsite->getId();
        }else{
            $addition = '/store/'.$this->_storeManager->getStore()->getId();
        }
        foreach($dirs as $dir){
            $code = explode('/',$dir);
            $code = end($code);
            $id = 'Codazon_ThemeOptions::'.$code.'_options';
            $params[$id] = [
                'type'  => 'add',
                'id'    => $id,
                'title' => $code.' Options',
                'module'=> 'Codazon_ThemeOptions',
                'action'=> 'themeoptions/config/edit/code/'.$code.'/section/general_section/theme_id/'.$this->getThemeId().$addition,
                'resource'=> 'Codazon_Options::themes_options'
            ];
        }
        $parent = $menu->get('Codazon_Options::themes_options');
        foreach($params as $id => $param){
            $item = $this->_itemFactory->create($param);
            $parent->getChildren()->add($item,null,10);
        }
        
        return $menu;
    }
}
