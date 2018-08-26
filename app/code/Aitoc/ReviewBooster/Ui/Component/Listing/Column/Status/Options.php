<?php
/**
 * Copyright © 2016 Aitoc. All rights reserved.
 */
namespace Aitoc\ReviewBooster\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->options = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'sent', 'label' => 'Sent'],
            ['value' => 'hold', 'label' => 'Hold']
        ];

        return $this->options;
    }
}
