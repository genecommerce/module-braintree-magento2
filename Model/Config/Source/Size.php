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
            ['value' => 0, 'label' => __('Small')],
            ['value' => 1, 'label' => __('Medium')],
            ['value' => 2, 'label' => __('Large')],
            ['value' => 3, 'label' => __('Responsive')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Small'), 1 => __('Medium'), 2 => __('Large'), 3 => __('Responsive')];
    }
}
