<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\LayoutProcessor\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use MSP\ReCaptcha\Model\LayoutSettings;

/**
 * Provides reCaptcha component configuration.
 */
class Onepage implements LayoutProcessorInterface
{
    /**
     * @var LayoutSettings
     */
    private $layoutSettings;

    /**
     * Onepage constructor.
     * @param LayoutSettings $layoutSettings
     */
    public function __construct(
        LayoutSettings $layoutSettings
    ) {
        $this->layoutSettings = $layoutSettings;
    }

    /**
     * Process js Layout of block
     *
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
        ['payment']['children']['payments-list']['children']['braintree-recaptcha']['children']
        ['msp_recaptcha_braintree']['settings'] = $this->layoutSettings->getCaptchaSettings();

        return $jsLayout;
    }
}
