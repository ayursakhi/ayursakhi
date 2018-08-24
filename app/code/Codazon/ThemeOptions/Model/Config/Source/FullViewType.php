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

class FullViewType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'full_screen', 'label' => __('Full Screen')],
            ['value' => 'pop_up', 'label' => __('Pop-up')]
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
		    'full_screen' => __('Full Screen'),
		    'pop_up' => __('Popup')
        ];
    }
}
