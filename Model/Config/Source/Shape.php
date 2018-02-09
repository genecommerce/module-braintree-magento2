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
}
