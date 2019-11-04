<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Ui\PayPal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as CreditConfig;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class ConfigProvider
 * @package Magento\Braintree\Model\Ui\PayPal
 */
class ConfigProvider implements ConfigProviderInterface
{
    const PAYPAL_CODE = 'braintree_paypal';
    const PAYPAL_CREDIT_CODE = 'braintree_paypal_credit';
    const PAYPAL_VAULT_CODE = 'braintree_paypal_vault';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ResolverInterface
     */
    private $resolver;

    /**
     * @var CreditConfig
     */
    private $creditConfig;

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param CreditConfig $creditConfig
     * @param ResolverInterface $resolver
     */
    public function __construct(
        Config $config,
        CreditConfig $creditConfig,
        ResolverInterface $resolver
    ) {
        $this->config = $config;
        $this->creditConfig = $creditConfig;
        $this->resolver = $resolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::PAYPAL_CODE => [
                    'isActive' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'locale' => $this->resolver->getLocale(),
                    'paymentAcceptanceMarkSrc' =>
                        'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png',
                    'vaultCode' => self::PAYPAL_VAULT_CODE,
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT)
                    ]
                ],

                self::PAYPAL_CREDIT_CODE => [
                    'isActive' => $this->creditConfig->isActive(),
                    'title' => __('PayPal Credit'),
                    'isAllowShippingAddressOverride' => $this->config->isAllowToEditShippingAddress(),
                    'merchantName' => $this->config->getMerchantName(),
                    'locale' => $this->resolver->getLocale(),
                    'paymentAcceptanceMarkSrc' =>
                        'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppc-acceptance-medium.png',
                    'paymentIcon' => $this->config->getPayPalIcon(),
                    'style' => [
                        'shape' => $this->config->getButtonShape(Config::BUTTON_AREA_CHECKOUT),
                        'size' => $this->config->getButtonSize(Config::BUTTON_AREA_CHECKOUT),
                        'color' => $this->config->getButtonColor(Config::BUTTON_AREA_CHECKOUT)
                    ]
                ]
            ]
        ];
    }
}
