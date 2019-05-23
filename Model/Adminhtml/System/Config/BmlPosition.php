<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\Adminhtml\System\Config;

/**
 * Class BmlPosition
 * @package Magento\Braintree\Model\Adminhtml\System\Config
 */
class BmlPosition
{
    /**
     * Bml positions source getter for Home Page
     *
     * @return array
     */
    public function getBmlPositionsHP(): array
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
    }

    /**
     * Bml positions source getter for Catalog Category Page
     *
     * @return array
     */
    public function getBmlPositionsCCP(): array
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Sidebar (right)')
        ];
    }

    /**
     * Bml positions source getter for Catalog Product Page
     *
     * @return array
     */
    public function getBmlPositionsCPP(): array
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Near the add to cart button')
        ];
    }

    /**
     * Bml positions source getter for Checkout Cart Page
     *
     * @return array
     */
    public function getBmlPositionsCheckout(): array
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Near proceed to checkout button')
        ];
    }
}
