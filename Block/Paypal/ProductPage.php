<?php

namespace Magento\Braintree\Block\Paypal;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Payment\Model\MethodInterface;

/**
 * Class ProductPage
 * @package Magento\Braintree\Block\Paypal
 */
class ProductPage extends Button
{
    /**
     * @var Registry
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
     * @param Registry $registry
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
        Registry $registry,
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
    public function isActive(): bool
    {
        if (parent::isActive() === true) {
            return $this->config->getProductPageBtnEnabled();
        }

        return false;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCurrency(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        /** @var Product $product */
        $product = $this->registry->registry('product');
        if ($product) {
            if ($product->getTypeId() === Configurable::TYPE_CODE) {
                $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount();
                return $price->getBaseAmount();
            }
            if ($product->getTypeId() === Grouped::TYPE_CODE) {
                $groupedProducts = $product->getTypeInstance()->getAssociatedProducts($product);
                return $groupedProducts[0]->getPrice();
            }

            return $product->getPrice();
        }

        return 100; // TODO There must be a better return value than this?
    }

    /**
     * @return string
     */
    public function getContainerId(): string
    {
        return 'oneclick';
    }

    /**
     * @return string
     */
    public function getActionSuccess(): string
    {
        return $this->getUrl('braintree/paypal/oneclick', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getButtonShape(): string
    {
        return $this->config->getButtonShape(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getButtonColor(): string
    {
        return $this->config->getButtonColor(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getButtonSize(): string
    {
        return $this->config->getButtonSize(Config::BUTTON_AREA_PDP);
    }

    /**
     * @inheritDoc
     */
    public function getDisabledFunding(): array
    {
        return [
            'card' => $this->config->getDisabledFundingOptionCard(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP),
            'elv' => $this->config->getDisabledFundingOptionElv(Config::KEY_PAYPAL_DISABLED_FUNDING_PDP)
        ];
    }
}
