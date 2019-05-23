<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DisabledFundingOptions
 * @package Magento\Braintree\Model\Adminhtml\Source
 */
class DisabledFundingOptions implements ArrayInterface
{
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => 'card',
                'label' => __('PayPal Guest Checkout Credit Card Icons'),
            ],
            [
                'value' => 'elv',
                'label' => __('Elektronisches Lastschriftverfahren – German ELV')
            ]
        ];
    }
}
