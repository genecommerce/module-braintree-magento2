<?php

namespace Magento\Braintree\Model\Config\Source;

/**
 * Class Size
 * @package Magento\Braintree\Model\Config\Source
 * @author Muj <muj@gene.co.uk>
 */
class Size implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Medium')],
            ['value' => 1, 'label' => __('Large')],
            ['value' => 2, 'label' => __('Responsive')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Medium'), 1 => __('Large'), 2 => __('Responsive')];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     * @return array
     */
    public function toRawValues()
    {
        return [
            0 => 'medium',
            1 => 'large',
            2 => 'responsive'
        ];
    }
}

