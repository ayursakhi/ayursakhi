<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    
    protected $coreRegistry;
    
    protected $storeManager;
    
    protected $resultForwardFactory;
        
    protected $lookbookFactory;
    
    protected $categoryFactory;
    
    protected $itemFactory;
    
    protected $helper;
    
    protected $scopeConfig;
    
    protected $storeId;
    
    protected $urlBuilder;
    
    protected $productCollectionFactory;
    
    protected $catalogProductVisibility;
    
    protected $layout;
    
    protected $objectManager;
    
    protected $categoryBaseUrl;
    
    protected $_useRewrites;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Codazon\Lookbookpro\Model\LookbookCategoryFactory $categoryFactory,
        \Codazon\Lookbookpro\Model\LookbookFactory $lookbookFactory,
        \Codazon\Lookbookpro\Model\LookbookItemFactory $itemFactory,
        \Codazon\Lookbookpro\Helper\Image $imageHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->context = $context;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->storeId = $this->storeManager->getStore()->getId();
        $this->lookbookFactory = $lookbookFactory;
        $this->categoryFactory = $categoryFactory;
        $this->itemFactory = $itemFactory;
        $this->imageHelper = $imageHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->layout = $layout;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_useRewrites = $this->scopeConfig->getValue('web/seo/use_rewrites', 'store');
    }
    
    public function getUrl($path, $params = [])
    {
        return $this->urlBuilder->getUrl($path, $params);
    }
    
    public function getConfig($path)
    {
        return $this->scopeConfig->getValue($path, 'store');
    }
    
    public function getLoobookByCategory($category = false, $storeId = null, $recursive = false)
    {
        
        $categoryId = ($category === false)? 0 : $category->getId();
        
        
        if ($storeId === null) {
            $storeId = $this->storeId;
        }
        $collection = $this->lookbookFactory->create()->getCollection();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect([
            'name', 'description', 'thumbnail', 'cover', 'url_key'
        ]);
        $collection->getSelect()->joinLeft(
            ['ccl' => $collection->getTable('cdzlookbook_category_lookbook')],
            'e.entity_id = ccl.lookbook_id',
            ['category_id', 'position']
        )->group('e.entity_id');
        
        
        if (($category !== false) && $categoryId && $recursive) {
            $childrenIds = $category->getResource()->getChildren($category);
            $categoryIds = [$categoryId];
            if (count($childrenIds)) {
                $categoryIds = array_merge($categoryIds, $childrenIds);
            }
            $categoryIds = implode(',', $categoryIds);
            $collection->getSelect()->where("ccl.category_id IN ({$categoryIds})");
        } elseif ($categoryId) {
            $collection->getSelect()->where("ccl.category_id = {$categoryId}");
        }
        return $collection;
    }
    
    public function getItemsByLookbookId($lookbookId, $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeId;
        }
        $collection = $this->itemFactory->create()->getCollection();
        $collection->setStoreId($storeId);
        $collection->addAttributeToSelect([
            'name', 'description', 'item_data'
        ]);
        $collection->getSelect()->joinLeft(
            ['ccl' => $collection->getTable('cdzlookbook_item_group')],
            'e.entity_id = ccl.item_id',
            ['lookbook_id', 'position']
        )->order('ccl.position asc')
        ->order('e.entity_id desc')
        ->group('e.entity_id');
        
        if ($lookbookId) {
            $collection->getSelect()->where("ccl.lookbook_id = {$lookbookId}");
        }
        return $collection;
    }
    
    public function getImageUrl($path, $width, $height = null, $basePath, $option = [])
    {
        $imagePath = $basePath . $path;
        return $this->imageHelper->init($imagePath)->resize($width, $height)->__toString();
    }
    
    public function getCategoryThumbnailUrl($category, $width, $height = null)
    {
        $path = $category->getThumbnail();
        return $this->getImageUrl($path, $width, $height, 'codazon/lookbook/category');
    }
    
    public function getCategoryCoverUrl($category, $width, $height = null)
    {
        $path = $category->getCover();
        return $this->getImageUrl($path, $width, $height, 'codazon/lookbook/category');
    }
    
    public function getLookbookThumbnailUrl($lookbook, $width, $height = null)
    {
        $path = $lookbook->getThumbnail();
        return $this->getImageUrl($path, $width, $height, 'codazon/lookbook/item');
    }
    
    public function getLookbookCoverUrl($lookbook, $width, $height = null)
    {
        $path = $lookbook->getCover();
        return $this->getImageUrl($path, $width, $height, 'codazon/lookbook/item');
    }
    
    public function getItemImageUrl($item, $width, $height = null)
    {
        $data = json_decode($item->getData('item_data'), true);
        $path = (!empty($data['image']))?$data['image']:'';
        return $this->getImageUrl($path, $width, $height, 'codazon/lookbook/item_element');
    }
    
    public function getLookbookUrl($lookbook, $category = null)
    {
        if ($this->_useRewrites) {
            if ($category) {
                $categoryPath = $category->getData('url_path');
                if ($categoryPath && ($category->getId() != $this->getStoreRootCategoryId())) {
                    return $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook/' . str_replace('.html', '', $categoryPath) . '/' . $lookbook->getUrlKey() . '.html']);
                } else {
                    return $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook/' . $lookbook->getUrlKey() . '.html']);
                }
            } else {
                return $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook/' . $lookbook->getUrlKey() . '.html']);
            }
        } else {
            return $this->getUrl('lookbooks/lookbook/view', ['id' => $lookbook->getId()]);
        }
    }
    
    public function getCategoryUrl($category)
    {
        if ($this->_useRewrites) {
            return $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook/category/' . $category->getUrlPath()]);
        } elseif ($category->getId()) {
            return $this->getUrl('lookbooks/category/view', ['id' => $category->getId()]);
        }
    }
    
    public function getCategoryBasedUrl() {
        if (!$this->categoryBaseUrl) {
            if ($this->_useRewrites) {
                $this->categoryBaseUrl =  $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook']);
            } else {
                $this->categoryBaseUrl = $this->getUrl('lookbooks/category/view');
            }
        }
        return $this->categoryBaseUrl;
    }
    
    public function getCategoryUrlByKey($urlKey, $id)
    {
        if ($this->_useRewrites) {
            return $this->getUrl(null, ['_nosid' => true, '_direct' => 'lookbook/category/' . $urlKey]);
        } else {
            return $this->getUrl('lookbooks/category/view', ['id' => $id]);
        }
    }
    
    public function getItemPoints($item)
    {
        $points = $item->getData('item_data');
        if ($points) {
            $points = json_decode($points, true);
            $points = !empty($points['points'])?$points['points']:false;
        }
        return $points;
    }
    
    public function getProductCollectionFactory()
    {
        if (empty($this->productCollectionFactory)) {
            $this->productCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        }
        return $this->productCollectionFactory;
    }
    
    protected function _addProductAttributesAndPrices(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection,
        $attributeToSelect = ['name', 'thumbnail', 'small_image']
    ) {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->addAttributeToSelect($attributeToSelect);
    }
    
    public function getPriceRender()
    {
        if (empty($this->priceRender)) {
            $this->priceRender = $this->layout->getBlock('product.price.render.default');
        }
        return $this->priceRender;
    }
    
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
    
    public function getProductCollectionByItemCollection($itemCollection, $width = 300, $height = 300, $attributeToSelect = ['name', 'thumbnail', 'small_image']) {
        $products = [0];
        $collection = $this->productCollectionFactory->create();
        
        foreach ($itemCollection as $item) {
            $points = $this->getItemPoints($item);
            if ($points) {
                $products = array_merge($products, array_column($points, 'productId'));
            }
        }
        $products = array_unique($products);
        
        $imageHelper = $this->objectManager->get('Magento\Catalog\Helper\Image');
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());
        $collection->getSelect()->limit(false);
        $this->_addProductAttributesAndPrices($collection, $attributeToSelect);
        $collection->addFieldToFilter('entity_id', ['in' => $products]);
        
        
        if ($collection->count()) {
            foreach ($collection as $product) {
                $product->addData([
                    'thumbnail_url'     => $imageHelper->init($product, 'product_thumbnail_image')
                        ->setImageFile($product->getData('thumbnail'))->resize($width, $height)->getUrl(),
                    'small_image_url'   => $imageHelper->init($product, 'product_small_image')
                        ->setImageFile($product->getData('small_image'))->resize($width, $height)->getUrl(),
                    'price_html'        => $this->getProductPriceHtml($product)
                ]);
            }
        }
        return $collection;
    }
    
    
    
    public function subString($str, $strLenght)
    {
        $str = $this->stripTags($str);
        if(strlen($str) > $strLenght) {
            $strCutTitle = substr($str, 0, $strLenght);
            $str = substr($strCutTitle, 0, strrpos($strCutTitle, ' '))."&hellip;";
        }
        return $str;
    }
    
    public function getLookbookRootCategoryId()
    {
        return \Codazon\Lookbookpro\Model\LookbookCategory::TREE_ROOT_ID;
    }
    
    public function getStoreRootCategoryId()
    {
        return $this->scopeConfig->getValue('codazon_lookbook/general/root_category', 'store');
    }
    
}
