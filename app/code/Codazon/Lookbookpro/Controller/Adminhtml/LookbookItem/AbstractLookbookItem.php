<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Controller\Adminhtml\LookbookItem;

class AbstractLookbookItem extends \Magento\Backend\App\Action
{
	protected $primary = 'entity_id';
    protected $modelClass = 'Codazon\Lookbookpro\Model\LookbookItem';
    
    public function execute()
    {
        /* TO DO */
    }
}

