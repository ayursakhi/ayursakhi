<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Codazon\ThemeOptions\Model\Config\Source;

class BlogGridType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'normal', 'label' => __('Normal')],
            ['value' => 'isotope', 'label' => __('Isotope Grid')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
		    'normal' => __('Normal'),
		    'isotope' => __('Isotope Grid')
        ];
    }
}
