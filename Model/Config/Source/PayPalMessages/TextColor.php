<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Config\Source\PayPalMessages;

use Magento\Framework\Data\OptionSourceInterface;

class TextColor implements OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'black', 'label' => __('black')],
            ['value' => 'white', 'label' => __('white')],
            ['value' => 'monochrome', 'label' => __('monochrome')],
            ['value' => 'grayscale', 'label' => __('grayscale')]
        ];
    }
}
