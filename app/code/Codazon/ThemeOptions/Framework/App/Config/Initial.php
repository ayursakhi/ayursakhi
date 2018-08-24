<?php
/**
 * Initial configuration data container. Provides interface for reading initial config values
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Framework\App\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Initial
{
    /**
     * Cache identifier used to store initial config
     */
    const CACHE_ID = 'codazon_initial_config';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Config metadata
     *
     * @var array
     */
    protected $_metadata = [];

    /**
     * @param \Magento\Framework\App\Config\Initial\Reader $reader
     * @param \Magento\Framework\App\Cache\Type\Config $cache
     */
    public function __construct(
        \Codazon\ThemeOptions\Framework\App\Config\Initial\Reader $reader,
        \Magento\Theme\Model\Design $design,
        \Magento\Framework\App\Config $scopeConfig,
        \Magento\Framework\App\Cache\Type\Config $cache,
        \Magento\Theme\Model\ResourceModel\Theme\Collection $themeCollection
    ) {
        $this->reader = $reader;
        $this->scopeConfig = $scopeConfig;
        $this->cache = $cache;
    }

    /**
     * Get initial data by given scope
     *
     * @param string $scope Format is scope type and scope code separated by pipe: e.g. "type|code"
     * @return array
     */
    public function getData($scope)
    {

        list($scopeType, $scopeId) = array_pad(explode('|', $scope), 2, null);
        $this->themeId = $this->scopeConfig->getValue(\Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,$scopeType,$scopeId);
        $cacheKey = self::CACHE_ID.'|theme|'.$this->themeId;
        $data = $this->cache->load($cacheKey);
        if (!$data) {
            $data = $this->reader->read($this->themeId);
            $this->cache->save(serialize($data), $cacheKey);
        } else {
            $data = unserialize($data);
        }
        if($data){
            $this->_data = $data['data'];
            $this->_metadata = $data['metadata'];
        }
        if(isset($this->_data['default'])){
            return $this->_data['default'];
        }else{
            return [];
        }
    }

    /**
     * Get configuration metadata
     *
     * @return array
     */
    public function getMetadata()
    {
        return $this->_metadata;
    }
}
