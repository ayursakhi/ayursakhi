<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Model;

class LookbookItem extends \Codazon\Lookbookpro\Model\AbstractModel
{

    const ENTITY = 'cdzlookbook_item';
    
    const CACHE_TAG = self::ENTITY;
    
    const CACHE_LOOKBOOK_ITEM_TAG = 'cdzlookbook_item_group';
    
    protected function _construct()
    {
        $this->_init('Codazon\Lookbookpro\Model\ResourceModel\LookbookItem');
    }
}
