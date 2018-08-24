<?php
/**
* Copyright © 2018 Codazon. All rights reserved.
* See COPYING.txt for license details.
*/

namespace Codazon\Lookbookpro\Ui\Component\Listing\Column;

class LookbookActions extends \Codazon\Lookbookpro\Ui\Component\Listing\Column\AbstractActions
{
	/** Url path */
	protected $_editUrl = 'lookbookpro/lookbook/edit';
    /**
    * @var string
    */
	protected $_deleteUrl = 'lookbookpro/lookbook/delete';
    /**
    * @var string
    */
    protected $_primary = 'entity_id';
    
    protected $_preview = true;
}

