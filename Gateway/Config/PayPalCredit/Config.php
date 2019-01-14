<?php

namespace Magento\Braintree\Gateway\Config\PayPalCredit;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Paypal\Model\Config as PPConfig;

/**
 * Class Config
 */
class Config implements ConfigInterface
{
    const KEY_ACTIVE = 'active';
    const KEY_UK_ACTIVATION_CODE = 'uk_activation_code';
    const KEY_UK_MERCHANT_NAME = 'uk_merchant_name';
    const KEY_CLIENT_ID = 'client_id';
    const KEY_SECRET = 'secret';
    const KEY_SANDBOX = 'sandbox';

    const DEFAULT_PATH_PATTERN = 'payment/%s/%s';

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
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getValue($field, $storeId = null)
    {
        if ($this->methodCode === null || $this->pathPattern === null) {
            return null;
        }

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $field
     * @return mixed
     */
    public function getConfigValue($field)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        // Only allowed on US and UK
        if (!$this->isUk() && !$this->isUS()) {
            return false;
        }

        // Validate configuration if UK
        if ($this->isUk()) {
            $merchantId = substr($this->getConfigValue("payment/braintree/merchant_id"), -4);
            if ($merchantId == $this->getActivationCode() && $this->getMerchantName()) {
                return true;
            }
            return false;
        } else {
            return (bool)$this->getValue(self::KEY_ACTIVE);
        }
    }

    /**
     * Calculator is only used on UK view
     * @return bool
     */
    public function isCalculatorEnabled()
    {
        return ($this->isUk() && $this->isActive());
    }

    /**
     * UK Merchant Name
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getValue(self::KEY_UK_MERCHANT_NAME);
    }

    /**
     * UK Activation Code
     * @return string
     */
    public function getActivationCode()
    {
        return $this->getValue(self::KEY_UK_ACTIVATION_CODE);
    }

    /**
     * PayPal Sandbox mode
     * @return bool
     */
    public function getSandbox()
    {
        return $this->getConfigValue('payment/braintree/environment') === 'sandbox';
    }

    /**
     * Client ID
     * @return string
     */
    public function getClientId()
    {
        return $this->getValue(self::KEY_CLIENT_ID);
    }

    /**
     * Secret Key
     * @return string
     */
    public function getSecret()
    {
        return $this->getValue(self::KEY_SECRET);
    }

    /**
     * Merchant Country set to GB/UK
     * @return bool
     */
    public function isUk()
    {
        return $this->getMerchantCountry() == "GB";
    }

    /**
     * Merchant Country set to US
     * @return bool
     */
    public function isUS()
    {
        return $this->getMerchantCountry() == "US";
    }

    /**
     * Merchant Country
     * @return bool
     */
    public function getMerchantCountry()
    {
        return $this->getConfigValue('paypal/general/merchant_country');
    }

    /**
     * Get Display option from stored config
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlDisplay($section)
    {
        return $this->getConfigValue('payment/' . PPConfig::METHOD_WPP_BML . '/' . $section . '_display');
    }

    /**
     * Get Position option from stored config
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlPosition($section)
    {
        return $this->getConfigValue('payment/' . PPConfig::METHOD_WPP_BML . '/' . $section . '_position');
    }

    /**
     * Get Size option from stored config
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlSize($section)
    {
        return $this->getConfigValue('payment/' . PPConfig::METHOD_WPP_BML . '/' . $section . '_size');
    }
}
