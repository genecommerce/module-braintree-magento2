<?php

namespace Magento\Braintree\Block\Paypal;

use Magento\Braintree\Gateway\Config\PayPal\Config;

/**
 * Class ProductPage
 * @package Magento\Braintree\Block\Paypal
 */
class ProductPage extends Paypal\Button
{
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
        return "27.00"; // @todo not hardcoded
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
