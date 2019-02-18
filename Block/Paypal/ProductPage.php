<?php

namespace Magento\Braintree\Block\Paypal;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/**
 * Class ProductPage
 * @package Magento\Braintree\Block\Paypal
 */
class ProductPage extends Button
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ProductPage constructor.
     * @param Context $context
     * @param ResolverInterface $localeResolver
     * @param Session $checkoutSession
     * @param Config $config
     * @param PayPalCreditConfig $payPalCreditConfig
     * @param BraintreeConfig $braintreeConfig
     * @param ConfigProvider $configProvider
     * @param MethodInterface $payment
     * @param \Magento\Framework\Registry $registry
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
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $localeResolver,
            $checkoutSession,
            $config,
            $payPalCreditConfig,
            $braintreeConfig,
            $configProvider,
            $payment,
            $data
        );

        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function isActive()
    {
        if (parent::isActive() === true) {
            return $this->config->getProductPageBtnEnabled();
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        $product = $this->registry->registry('product');
        if ($product) {
            /** @var $product \Magento\Catalog\Model\Product */
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                return $product->getPriceInfo()->getPrice('regular_price')->getAmount();
            }

            return $product->getPrice();
        }

        return 100;
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return 'oneclick';
    }

    /**
     * @return string
     */
    public function getActionSuccess()
    {
        return $this->getUrl('braintree/paypal/oneclick', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getButtonShape()
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_PDP);
    }

    /**
     * @return string
     */
    public function getButtonColor()
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_PDP);
    }

    /**
     * @return string
     */
    public function getButtonLayout()
    {
        return $this->config->getButtonLayout(Config::BUTTON_AREA_PDP);
    }

    /**
     * @return string
     */
    public function getButtonSize()
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_PDP);
    }

    /**
     * @return string
     */
    public function getDisabledFunding()
    {
        return [
            'card' => $this->config->getDisabledFundingOptionCard(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP),
            'elv' => $this->config->getDisabledFundingOptionElv(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP)
        ];
    }
}
