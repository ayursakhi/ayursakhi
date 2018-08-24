<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model\ResourceModel\LookbookItem;


class Collection extends \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
{
    protected function _construct() {
        $this->_init('Codazon\Lookbookpro\Model\LookbookItem', 'Codazon\Lookbookpro\Model\ResourceModel\LookbookItem');
    }
}
