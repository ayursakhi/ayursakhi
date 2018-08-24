<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SearchSuiteAutocomplete\Model\Source;

class AutocompleteFields
{
    const SUGGEST = 'suggest';

    const PRODUCT = 'product';

    /**
     *
     * @return array
     */
    public function toOptionArray()
    {
        $this->options = [
            ['value' => self::SUGGEST, 'label' => __('Suggested')],
            ['value' => self::PRODUCT, 'label' => __('Products')],
        ];
   
        return $this->options;
    }
}
