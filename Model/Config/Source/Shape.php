<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Shape implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'pill', 'label' => __('Pill')],
            ['value' => 'rect', 'label' => __('Rectangle')]
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
            'pill' => __('Pill'),
            'rect' => __('Rectangle')
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
            'pill' => 'pill',
            'rect' => 'rect',
        ];
    }
}
