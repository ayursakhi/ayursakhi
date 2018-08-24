<?php
namespace Codazon\Lookbookpro\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

class Router implements \Magento\Framework\App\RouterInterface
{
    protected $_actionFactory;

    protected $_storeManager;

    protected $_lookbookCollectionFactory;
    
    protected $_categoryCollectionFactory;

    protected $_scopeConfig;
    
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Codazon\Lookbookpro\Model\ResourceModel\Lookbook\CollectionFactory $lookbookCollectionFactory,
        \Codazon\Lookbookpro\Model\ResourceModel\LookbookCategory\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_actionFactory = $actionFactory;
        $this->_lookbookCollectionFactory = $lookbookCollectionFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = ltrim($request->getPathInfo(), '/');
        $urlKey = explode('/', $pathInfo);
        
        if (!empty($urlKey[1])) {
            $storeId = $this->_storeManager->getStore()->getId();            
            if ($urlKey[1] == 'category') {
                if (!empty($urlKey[2])) {
                    $path = substr($pathInfo, 0, strpos($pathInfo, '.html') + 5);
                    $path = ltrim(str_replace('lookbook/category', '', $path), '/');
                    $urlKey = $urlKey[2];
                    
                    $match = $this->_categoryCollectionFactory->create()
                        ->setStoreId($storeId)
                        ->addAttributeToFilter('url_path', $path);
                    if ($match->count()) {
                        $category = $match->getFirstItem();
                        if ($category->getId() == \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID) {
                            return null;
                        }
                        $request->setModuleName('lookbooks')
                            ->setControllerName('category')
                            ->setActionName('view')
                            ->setParam('id', $category->getId());
                        $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $pathInfo);
                        return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                } else {
                    $request->setModuleName('lookbooks')
                            ->setControllerName('category')
                            ->setActionName('view');
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, 'lookbook');
                    return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
                }
            } else {
                $path = substr($pathInfo, 0, strpos($pathInfo, '.html'));
                $urlKey = explode('/', $path);
                $urlKey = $urlKey[count($urlKey) - 1];
                $categoryPath = trim(str_replace([$urlKey . '.html', 'lookbook'], ['', ''], $pathInfo), '/');
                
                $category = $this->_categoryCollectionFactory->create()
                        ->addAttributeToSelect(['url_path', 'name'])
                        ->addAttributeToFilter('is_active', 1)
                        ->setStoreId($storeId)
                        ->addAttributeToFilter('url_path', $categoryPath . '.html');
                
                $match = $this->_lookbookCollectionFactory->create()
                        ->setStoreId($storeId)
                        ->addAttributeToFilter('url_key', $urlKey);
                
                if ($match->count()) {
                    $request->setModuleName('lookbooks')
                            ->setControllerName('lookbook')
                            ->setActionName('view')
                            ->setParam('id', $match->getFirstItem()->getId());
                    
                    if ($categoryPath && $category->count()) {
                        $request->setParam('cat_id', $category->getFirstItem()->getId());
                        \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry')->register('lookbook_category', $category->getFirstItem());
                    }
                    
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, 'lookbook/'.$pathInfo);
                    return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
                }
            }
        } else {
            if ($urlKey[0] == 'lookbook') {
                $request->setModuleName('lookbooks')
                    ->setControllerName('category')
                    ->setActionName('view');
                $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, 'lookbook');
                return $this->_actionFactory->create('Magento\Framework\App\Action\Forward');
            }
        }
        
        return null;
    }
}
