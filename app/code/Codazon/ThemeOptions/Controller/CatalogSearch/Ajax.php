<?php
/**
 * Copyright © 2017 Codazon. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Codazon\ThemeOptions\Controller\CatalogSearch;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

class Ajax extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Display search result
     *
     * @return void
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();
        $query->setStoreId($this->_storeManager->getStore()->getId());
        $catId = $this->getRequest()->getParam('cat');
        
        if ($query->getQueryText() != '') {
            

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $category = $objectManager->create('Magento\Catalog\Model\Category')->load($catId);
            $productCollection = $this->layerResolver->get()->setCurrentCategory($catId)
                ->getProductCollection()
                //->addCategoryFilter($category)
                ->addSearchFilter($query->getQueryText());
            //$productCollection->getSelect()->where('cat_index.category_id = ' . $catId);
            
            echo $productCollection->getSelect();
            echo "<pre>";
            print_r(get_class_methods($this->layerResolver->get()));
            die();
        
            if ($this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
            $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();
        }
    }
    
    
}
