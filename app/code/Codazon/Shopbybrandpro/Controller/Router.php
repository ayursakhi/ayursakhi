<?php
namespace Codazon\Shopbybrandpro\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected $actionFactory;

    protected $_storeManager;

    protected $_brandFactory;

    protected $_scopeConfig;
    
    protected $_attributeCode;
    
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Codazon\Shopbybrandpro\Model\BrandFactory $brandFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->actionFactory = $actionFactory;
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_attributeCode = $this->_scopeConfig->getValue('codazon_shopbybrand/general/attribute_code');
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        
        $pathInfo = trim($request->getPathInfo(), '/');
        $urlKey = explode('/', $pathInfo);
        
        if (isset($urlKey[1]) && ($urlKey[0] == 'brand')) {
            $urlKey = strtolower(urldecode($urlKey[1]));
            $storeId = $this->_storeManager->getStore()->getId();
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
                        
            $brandCollection = $this->_brandFactory->create()
                ->getCollection();
            
            $optionValueTable = $brandCollection->getTable('eav_attribute_option_value');
            $select = $brandCollection->getConnection()->select();
            $select->from($optionValueTable, ['option_id']);
            $select->where($optionValueTable.'.store_id IN ('.$defaultStoreId.', '.$storeId.')')
                ->where("LOWER(REPLACE(RTRIM(value), ' ', '-')) = '{$urlKey}'")
                ->order('store_id DESC')
                ->limit(1);
                
            $brand = $brandCollection->getConnection()->fetchRow($select);
            if ($brand) {
                $request->setModuleName('brands')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam($this->_attributeCode, $brand['option_id']);
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, 'brand/'.$urlKey);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
            
            $brandCollection->setStore($storeId)
                ->addAttributeToFilter('brand_url_key', $urlKey);
            $brand = $brandCollection->getFirstItem();
            
            if ($brand->getId()) {
                $request->setModuleName('brands')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam($this->_attributeCode, $brand->getOptionId());
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, 'brand/'.$urlKey);
                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
            }
        }
        return null;
    }
}
