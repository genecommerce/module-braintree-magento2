<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace  Magento\Braintree\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Gateway\Config\Config;

/**
 * Adds reCaptcha configuration to checkout.
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'msp_recaptcha_braintree' => $this->config->getCaptchaSettings(),
        ];
    }
}