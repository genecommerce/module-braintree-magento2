<?php

namespace Magento\Braintree\Model\Config\Source;

/**
 * Class Color
 * @package Magento\Braintree\Model\Config\Source
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Color implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Blue')],
            ['value' => 1, 'label' => __('Black')],
            ['value' => 2, 'label' => __('Gold')],
            ['value' => 3, 'label' => __('Silver')]
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
            0 => __('Blue'),
            1 => __('Black'),
            2 => __('Gold'),
            3 => __('Silver')
        ];
    }
}
