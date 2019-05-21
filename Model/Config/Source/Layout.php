<?php

namespace Magento\Braintree\Model\Config\Source;

/**
 * Class Layout
 * @package Magento\Braintree\Model\Config\Source
 * @author Muj <muj@gene.co.uk>
 */
class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Horizontal')],
            ['value' => 1, 'label' => __('Vertical')],

        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Horizontal'), 1 => __('Vertical')];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     * @return array
     */
    public function toRawValues()
    {
        return [
            0 => 'horizontal',
            1 => 'vertical',
        ];
    }
}
