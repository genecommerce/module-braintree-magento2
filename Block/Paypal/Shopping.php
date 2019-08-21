<?php
declare(strict_types=1);

namespace Magento\Braintree\Block\Paypal;

use Magento\Framework\View\Element\Template;

/**
 * Class Shopping
 *
 * @package Magento\Braintree\Block\Paypal
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class Shopping extends Template
{
    /**
     * Client ID from PayPal account.
     * Passed as a GET param when loading the PayPal JS SDK.
     *
     * @return string
     */
    public function getClientId(): string
    {
        return 'AYkryi9mte23ZuKo62tAXnyDikAYXCJcnMDcvOqspH8KmhORaSP6tqu3eCNWE3-twi1YIFXV-4Xwoibb';
    }

    /**
     * Passed as a GET param when loading the PayPal JS SDK.
     * This will be set in the config values, after the onboarding process.
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return '8YC9P5KG8RGUQ';
    }

    /**
     * Property ID used when setting up the PayPal Tracker object.
     * This will be set in the config values, after the onboarding process.
     *
     * @return string
     */
    public function getPropertyId(): string
    {
        return 'cc2d8b95-39b0-430f-a9cf-4f6f5896f018';
    }
}
