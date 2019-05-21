<?php

namespace Magento\Braintree\Model\Config\Source;

/**
 * Class Shape
 * @package Magento\Braintree\Model\Config\Source
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Shape implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Pill')], ['value' => 1, 'label' => __('Rectangle')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Pill'), 1 => __('Rectangle')];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     * @return array
     */
    public function toRawValues()
    {
        return [
            0 => 'pill',
            1 => 'rect',
        ];
    }
}
