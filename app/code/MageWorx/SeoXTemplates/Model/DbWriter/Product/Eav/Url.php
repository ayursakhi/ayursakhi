<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DbWriter\Product\Eav;

use Magento\Framework\App\ResourceConnection;
use MageWorx\SeoXTemplates\Model\DataProviderProductFactory;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use MageWorx\SeoAll\Helper\LinkFieldResolver;
use Magento\Catalog\Api\Data\ProductInterface;

class Url extends \MageWorx\SeoXTemplates\Model\DbWriter\Product\Eav
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    public function __construct(
        ResourceConnection $resource,
        DataProviderProductFactory $dataProviderProductFactory,
        Product $productResource,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist,
        LinkFieldResolver $linkFieldResolver
    ) {
        parent::__construct($resource, $dataProviderProductFactory, $productResource, $linkFieldResolver);
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Insert proccess
     *
     * @param string $table
     * @param array $multipleData
     */
    protected function doInsert($table, $multipleData)
    {
        foreach ($multipleData as $data) {
            if (!empty($data)) {
                $this->_connection->insert($table, $data);
            }

            $this->updateProductUrl($data);
        }
    }

    /**
     * Update proccess
     *
     * @param string $table
     * @param array $multipleData
     */
    protected function doUpdate($table, $multipleData)
    {
        $linkField = $this->linkFieldResolver->getLinkField(ProductInterface::class, 'entity_id');
        if (!empty($multipleData)) {
            foreach ($multipleData as $data) {
                $where = [
                    'attribute_id = ?' => (int)$data['attribute_id'],
                     $linkField.' = ?' => (int)$data[$linkField],
                    'store_id =?'      => (int)$data['store_id']
                ];
                $bind = ['value' => $data['value']];
                $this->_connection->update($table, $bind, $where);

                $this->updateProductUrl($data);
            }
        }
    }

    /**
     * Update product urls and create rewrites
     *
     * @param array $data
     */
    protected function updateProductUrl($data)
    {
        $connect =  $this->dataProvider->getCollectionIds();
        $linkField = $this->linkFieldResolver->getLinkField(ProductInterface::class, 'entity_id');
        $product = $this->_collection->getItemById($connect[$data[$linkField]]);

        $this->urlPersist->deleteByData([
            UrlRewrite::ENTITY_ID => $product->getId(),
            UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
            UrlRewrite::REDIRECT_TYPE => 0,
            UrlRewrite::STORE_ID => $product->getStoreId()
        ]);

        $product->setUrlKey($data['value']);

        if ($product->isVisibleInSiteVisibility()) {
            $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
        }
    }
}
