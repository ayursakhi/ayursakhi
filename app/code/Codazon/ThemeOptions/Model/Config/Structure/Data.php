<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Model\Config\Structure;

class Data extends \Magento\Config\Model\Config\Structure\Data
{
    /**
     * @param Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId
    ) {
    	$code = 'Codazon_settings';
    	$uri = $_SERVER['REQUEST_URI'];
        $params = explode('/',$uri);
        for($i=0; $i < count($params); $i++){
        	if($params[$i] == 'theme_id'){
        		$code = ucwords($params[$i+1]);
        		break;
        	}
        }
        $this->themeId = $code;
        $cacheId = 'Codazon'.$code;
        parent::__construct($reader, $configScope, $cache, $cacheId);
    }


    /**
     * Merge additional config
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        if (isset($config['config']['system'])) {
            $config = $config['config']['system'];
        }
        parent::merge($config);
    }

    protected function _loadScopedData()
    {
        $scope = $this->_configScope->getCurrentScope();
        if (false == isset($this->_loadedScopes[$scope])) {
            if (false == in_array($scope, $this->_scopePriorityScheme)) {
                $this->_scopePriorityScheme[] = $scope;
            }
            foreach ($this->_scopePriorityScheme as $scopeCode) {
                if (false == isset($this->_loadedScopes[$scopeCode])) {
                    if ($scopeCode !== 'primary' && ($data = $this->_cache->load($scopeCode . '::' . $this->_cacheId))
                    ) {
                        $data = unserialize($data);
                    } else {
                        $data = $this->_reader->read($this->themeId);
                        if ($scopeCode !== 'primary') {
                            $this->_cache->save(serialize($data), $scopeCode . '::' . $this->_cacheId);
                        }
                    }
                    $this->merge($data);
                    $this->_loadedScopes[$scopeCode] = true;
                }
                if ($scopeCode == $scope) {
                    break;
                }
            }
        }
    }
}
