<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\Paypal;

use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;
use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

/**
 * Class Button
 */
class Button extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeConfig
     */
    private $braintreeConfig;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var MethodInterface
     */
    private $payment;

    /**
     * @var PayPalCreditConfig
     */
    private $payPalCreditConfig;

    /**
     * Button constructor.
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param Config $config
     * @param PayPalCreditConfig $payPalCreditConfig
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param MethodInterface $payment
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResolverInterface $localeResolver,
        Session $checkoutSession,
        Config $config,
        PayPalCreditConfig $payPalCreditConfig,
        BraintreeConfig $braintreeConfig,
        ConfigProvider $configProvider,
        MethodInterface $payment,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->localeResolver = $localeResolver;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->braintreeConfig = $braintreeConfig;
        $this->configProvider = $configProvider;
        $this->payment = $payment;
        $this->payPalCreditConfig = $payPalCreditConfig;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml() // @codingStandardsIgnoreLine
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->checkoutSession->getQuote()->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->payment->isAvailable($this->checkoutSession->getQuote()) &&
            $this->config->isDisplayShoppingCart();
    }

    /**
     * @return bool
     */
    public function isCreditActive()
    {
        return $this->payPalCreditConfig->isActive();
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }

    /**
     * @return string
     */
    public function getPayeeEmail()
    {
        return $this->config->getPayeeEmail();
    }

    /**
     * @return string
     */
    public function getButtonShape()
    {
        return $this->config->getButtonShape();
    }

    /**
     * @return string
     */
    public function getButtonColor()
    {
        return $this->config->getButtonColor();
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->braintreeConfig->getEnvironment();
    }

    /**
     * @return string|null
     */
    public function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    /**
     * @return string
     */
    public function getActionSuccess()
    {
        return $this->getUrl(ConfigProvider::CODE . '/paypal/review', ['_secure' => true]);
    }
}
