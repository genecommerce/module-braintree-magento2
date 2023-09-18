<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CreditColor implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'darkblue', 'label' => __('Dark Blue')],
            ['value' => 'white', 'label' => __('White')],
            ['value' => 'black', 'label' => __('Black')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'darkblue' => __('Dark Blue'),
            'white' => __('White'),
            'black' => __('Black')
        ];
    }

    /**
     * Values in the format needed for the PayPal JS SDK
     *
     * @return array
     */
    public function toRawValues(): array
    {
        return [
            'darkblue' => 'darkblue',
            'white' => 'white',
            'black' => 'black',
        ];
    }
}
