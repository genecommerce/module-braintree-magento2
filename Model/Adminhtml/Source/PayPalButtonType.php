<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

class PayPalButtonType implements OptionSourceInterface
{
    /**
     * Possible actions on order place
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'paypal',
                'label' => __('PayPal Button'),
            ],
            [
                'value' => 'paylater',
                'label' => __('PayPal Pay Later Button'),
            ],
            [
                'value' => 'credit',
                'label' => __('PayPal Credit Button')
            ]
        ];
    }
}
