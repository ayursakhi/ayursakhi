<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use MageWorx\SeoCrossLinks\Model\Replacer;
use MageWorx\SeoCrossLinks\Helper\Data as HelperData;

/**
 * @method string getLinkTitle()
 * @method Crosslink setLinkTitle(\string $title)
 * @method string getKeyword()
 * @method Crosslink setkeyword(\string $keyword)
 * @method int getLinkTarget()
 * @method Crosslink setLinkTarget(\int $target)
 * @method int getStoreId()
 * @method array getStores()
 * @method Crosslink setStoreId(\int $storeId)
 * @method string getRefStaticUrl()
 * @method Crosslink setRefStaticUrl(\string $url)
 * @method string getRefProductSku()
 * @method Crosslink setRefProductSku(\string $sku)
 * @method string getRefCategoryId()
 * @method Crosslink setRefCategoryId(\string $sku)
 * @method bool getUseInProduct()
 * @method Crosslink setUseInProduct(\bool $isUse)
 * @method bool getUseInCategory()
 * @method Crosslink setUseInCategory(\bool $isUse)
 * @method bool getUseInCmsPage()
 * @method Crosslink setUseInCmsPage(\bool $isUse)
 * @method int getPriority()
 * @method Crosslink setPriority(\int $priority)
 * @method int getReplacementCount()
 * @method Crosslink setReplacementCount(\int $replacementCount)
 * @method int getIsActive()
 * @method Crosslink setIsActive(\int $status)
 * @method bool getNofollowRel()
 * @method Crosslink setNofillowRel(\bool $isUse)
 */
class Crosslink extends AbstractModel
{
    /**
     * Status enabled
     *
     * @var int
     */
    const STATUS_ENABLED   = 1;
    /**
     * Status disabled
     *
     * @var int
     */
    const STATUS_DISABLED  = 0;

    /**
     * Value for using only crosslink title
     */
    const USE_CROSSLINK_TITLE_ONLY = 0;

    /**
     * Value for using entity name for title
     */
    const USE_NAME_ALWAYS          = 1;

    /**
     * Value for using name of entity for title if crosslink title empty
     */
    const USE_NAME_IF_EMPTY_TITLE  = 2;


    /**
     * Value for link target
     */
    const TARGET_LINK_SELF  = 0;

    /**
     * Value for link target
     */
    const TARGET_LINK_BLANK = 1;

    /**
     * Value for using only crosslink title
     */
    const REFERENCE_TO_STATIC_URL = 0;

    /**
     * Value for using entity name for title
     */
    const REFERENCE_TO_PRODUCT_BY_SKU = 1;

    /**
     * Value for using entity name for title if crosslink title empty
     */
    const REFERENCE_TO_CATEGORY_BY_ID = 2;

    /**
     * Value for using entity name for title if crosslink title empty
     */
    const REFERENCE_TO_LANDINGPAGE_BY_ID = 3;


    /**
     * @var Url
     */
    protected $urlModel;
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageworx_seocrosslinks_crosslink';

    /**
     * cache tag
     *
     * @var string
     */
    protected $cacheTag = 'mageworx_seocrosslinks_crosslink';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageworx_seocrosslinks_crosslink';

    /**
     * filter model
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    protected $replacer;

    protected $helperData;


    public function __construct(
        FilterManager $filter,
        Context $context,
        Registry $registry,
        Replacer $replacer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HelperData $helperData,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->filter       = $filter;
        $this->replacer     = $replacer;
        $this->helperData   = $helperData;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('MageWorx\SeoCrossLinks\Model\ResourceModel\Crosslink');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get default crosslink values
     * @return array
     */
    public function getDefaultValues()
    {
        return [
            'link_target'       => $this->helperData->getDefaultLinkTarget(),
            'reference'         => $this->helperData->getDefaultReference(),
            'replacement_count' => $this->helperData->getDefaultReplacementCount(),
            'priority'          => $this->helperData->getDefaultPriority(),
            'in_product'        => $this->helperData->getDefaultForProductPage(),
            'in_category'       => $this->helperData->getDefaultForCategoryPage(),
            'in_cms_page'       => $this->helperData->getDefaultForCmsPageContent(),
            'in_landingpage'    => $this->helperData->getDefaultForLandingPageContent(),
            'nofollow_rel'      => $this->helperData->getDefaultForNofollowContent(),
            'is_active'         => $this->helperData->getDefaultStatus()
        ];
    }

    /**
     * @return \MageWorx_SeoCrossLinks_Model_Crosslink
     */
    protected function _afterLoad()
    {
        if ($this->getData('ref_static_url')) {
            $this->setData('reference', self::REFERENCE_TO_STATIC_URL);
        } elseif ($this->getData('ref_product_sku')) {
            $this->setData('reference', self::REFERENCE_TO_PRODUCT_BY_SKU);
        } elseif ($this->getData('ref_category_id')) {
            $this->setData('reference', self::REFERENCE_TO_CATEGORY_BY_ID);
        }
        elseif ($this->getData('ref_landingpage_id')) {
            $this->setData('reference', self::REFERENCE_TO_LANDINGPAGE_BY_ID);
        }
        return parent::_afterLoad();
    }

    /**
     *
     * @return \MageWorx_SeoCrossLinks_Model_Crosslink
     */
    public function beforeSave()
    {
        if (!$this->getData('reference')) {
            return parent::beforeSave();
        }

        switch ($this->getData('reference')) {
            case self::REFERENCE_TO_STATIC_URL:
                $this->setData('ref_product_sku', null);
                $this->setData('ref_category_id', null);
                $this->setData('ref_landingpage_id', null);
                break;
            case self::REFERENCE_TO_PRODUCT_BY_SKU:
                $this->setData('ref_static_url', null);
                $this->setData('ref_category_id', null);
                $this->setData('ref_landingpage_id', null);
                break;
            case self::REFERENCE_TO_CATEGORY_BY_ID:
                $this->setData('ref_static_url', null);
                $this->setData('ref_product_sku', null);
                $this->setData('ref_landingpage_id', null);
                break;
            case  self::REFERENCE_TO_LANDINGPAGE_BY_ID:
                $this->setData('ref_static_url', null);
                $this->setData('ref_product_sku', null);
                $this->setData('ref_category_id', null);
                break;
        }
        return parent::beforeSave();
    }

    /**
     * Replace keywords to links in html
     *
     * @param $entity
     * @param $html
     * @param $maxReplaceCount
     * @param null $ignoreProductSku
     * @param null $ignoreCategoryId
     * @param null $ignoreLandingPageId
     * @return bool|string
     */
    public function replace(
        $entity,
        $html,
        $maxReplaceCount,
        $ignoreProductSku = null,
        $ignoreCategoryId = null,
        $ignoreLandingPageId = null
    ) {
        $collection = $this->getCollection();

        switch ($entity) {
            case 'product':
                $collection->addInProductFilter();
                break;
            case 'category':
                $collection->addInCategoryFilter();
                break;
            case 'cms_page':
                $collection->addInCmsPageFilter();
                break;
            case 'landingpage':
                $collection->addInLandingPageFilter();
                break;
            default:
                return false;
        }

        $collection->addStoreFilter($this->storeManager->getStore()->getStoreId());
        $collection->addEnabledFilter();
        $collection->setOrder('priority');

        return $this->replacer->replace(
            $collection,
            $html,
            $maxReplaceCount,
            $ignoreProductSku,
            $ignoreCategoryId,
            $ignoreLandingPageId
        );
    }

    /**
     * Retrive Target Links Value
     *
     * @param int $num
     * @return string
     */
    public function getTargetLinkValue($num = 0)
    {
        switch ($num) {
            case self::TARGET_LINK_SELF:
                return '_self';
            case self::TARGET_LINK_BLANK:
                return '_blank';
        }
    }

}
