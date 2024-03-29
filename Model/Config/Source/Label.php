<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Label implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'paypal',
                'label' => __('Paypal'),
            ],
            [
                'value' => 'checkout',
                'label' => __('Checkout'),
            ],
            [
                'value' => 'buynow',
                'label' => __('Buynow'),
            ],
            [
                'value' => 'pay',
                'label' => __('Pay'),
            ]
        ];
    }
}
