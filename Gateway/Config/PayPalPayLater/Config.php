<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Braintree\Gateway\Config\PayPalPayLater;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    public const KEY_ACTIVE = 'active';
    public const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string|null
     */
    private $methodCode;

    /**
     * @var string|null
     */
    private $pathPattern;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string $methodCode = null,
        string $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * @inheritdoc
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * @inheritdoc
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Get configuration field value
     *
     * @param string $field
     * @return mixed
     */
    public function getConfigValue(string $field)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getValue($field, $storeId = null)
    {
        if (null === $this->methodCode || null === $this->pathPattern) {
            return null;
        }

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterActive = $this->getConfigValue("payment/braintree_paypal_paylater/active");

        // If PayPal or PayPal Pay Later is disabled in the admin
        if (!$paypalActive || !$paypalPayLaterActive) {
            return false;
        }

        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get PayPal pay later message configuration status
     *
     * @param string $buttonType
     * @return bool
     */
    public function isMessageActive(string $buttonType): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterMessageActive = $this->getConfigValue(
            "payment/braintree_paypal/button_location_" . $buttonType . "_type_messaging_show"
        );
        // If PayPal or PayPal Pay Later Message is disabled or PayPal Vault is enabled on checkout
        if (!$paypalActive || !$paypalPayLaterMessageActive || ($this->IsPayPalVaultActive() && $type == 'checkout')) {
            return false;
        }

        if (!in_array($this->getMerchantCountry(), ['GB','FR','US','DE', 'AU'])) {
            return false;
        }

        return (bool) $paypalPayLaterMessageActive;
    }

    /**
     * Get PayPal pay later button configuration status
     *
     * @param string $buttonType
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isButtonActive(string $buttonType): bool
    {
        $paypalActive = $this->getConfigValue("payment/braintree_paypal/active");
        $paypalPayLaterActive = $this->getConfigValue("payment/braintree_paypal_paylater/active");
        $paypalPayLaterButtonShow = $this->getConfigValue(
            "payment/braintree_paypal/button_location_checkout_type_paylater_show"
        );

        // If PayPal or PayPal Pay Later is disabled in the admin
        if (!$paypalActive || !$paypalPayLaterActive || !$paypalPayLaterButtonShow) {
            return false;
        }

        return (bool) $paypalPayLaterButtonShow;
    }

    /**
     * Merchant Country set to US
     *
     * @return bool
     */
    public function isUS(): bool
    {
        return 'US' === $this->getMerchantCountry();
    }

    /**
     * Merchant Country
     *
     * @return string|null
     */
    public function getMerchantCountry(): ?string
    {
        return $this->getConfigValue('paypal/general/merchant_country');
    }

    /**
     * Get PayPal Vault status
     *
     * @return bool
     */
    public function isPayPalVaultActive(): bool
    {
        return (bool) $this->getConfigValue('payment/braintree_paypal_vault/active');
    }
}
