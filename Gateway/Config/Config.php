<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Braintree\Model\StoreConfigResolver;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_MERCHANT_ACCOUNT_ID = 'merchant_account_id';
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_CC_TYPES_BRAINTREE_MAPPER = 'cctypes_braintree_mapper';
    const KEY_SDK_URL = 'sdk_url';
    const KEY_USE_CVV = 'useccv';
    const KEY_USE_CVV_VAULT = 'useccv_vault';
    const KEY_VERIFY_3DSECURE = 'verify_3dsecure';
    const KEY_THRESHOLD_AMOUNT = 'threshold_amount';
    const KEY_VERIFY_ALLOW_SPECIFIC = 'verify_all_countries';
    const KEY_VERIFY_SPECIFIC = 'verify_specific_countries';
    const VALUE_3DSECURE_ALL = 0;
    const CODE_3DSECURE = 'three_d_secure';
    const KEY_KOUNT_MERCHANT_ID = 'kount_id';
    const FRAUD_PROTECTION = 'fraudprotection';
    const FRAUD_PROTECTION_THRESHOLD = 'fraudprotection_threshold';

    /**
     * Get list of available dynamic descriptors keys
     * @var array
     */
    private static $dynamicDescriptorKeys = [
        'name', 'phone', 'url'
    ];

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * Config constructor.
     * @param StoreConfigResolver $storeConfigResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param null $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        StoreConfigResolver $storeConfigResolver,
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        $this->storeConfigResolver = $storeConfigResolver;
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountrySpecificCardTypeConfig()
    {
        $countryCardTypes = $this->getValue(
            self::KEY_COUNTRY_CREDIT_CARD,
            $this->storeConfigResolver->getStoreId()
        );
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(
            self::KEY_CC_TYPES,
            $this->storeConfigResolver->getStoreId()
        );

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Braintree card types
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCcTypesMapper()
    {
        $result = json_decode(
            $this->getValue(
                self::KEY_CC_TYPES_BRAINTREE_MAPPER,
                $this->storeConfigResolver->getStoreId()
            ),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get list of card types available for country
     * @param string $country
     * @return array
     */
    public function getCountryAvailableCardTypes($country)
    {
        $types = $this->getCountrySpecificCardTypeConfig();

        return (!empty($types[$country])) ? $types[$country] : [];
    }

    /**
     * Check if cvv field is enabled
     *
     * @return boolean
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isCvvEnabled()
    {
        return (bool) $this->getValue(
            self::KEY_USE_CVV,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Check if cvv field is enabled for vaulted cards
     *
     * @return boolean
     */
    public function isCvvEnabledVault()
    {
        return false;
    }

    /**
     * Check if 3d secure verification enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isVerify3DSecure()
    {
        return (bool) $this->getValue(
            self::KEY_VERIFY_3DSECURE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get threshold amount for 3d secure
     *
     * @return float
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getThresholdAmount()
    {
        return (double) $this->getValue(
            self::KEY_THRESHOLD_AMOUNT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get list of specific countries for 3d secure
     *
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get3DSecureSpecificCountries()
    {
        $keyVerifyAllowSpecific = $this->getValue(
            self::KEY_VERIFY_ALLOW_SPECIFIC,
            $this->storeConfigResolver->getStoreId()
        );
        if ((int) $keyVerifyAllowSpecific == self::VALUE_3DSECURE_ALL) {
            return [];
        }

        return explode(
            ',',
            $this->getValue(
                self::KEY_VERIFY_SPECIFIC,
                $this->storeConfigResolver->getStoreId()
            )
        );
    }

    /**
     * Get environment
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEnvironment()
    {
        return $this->getValue(
            Config::KEY_ENVIRONMENT,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Kount Merchant Id
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getKountMerchantId()
    {
        return $this->getValue(
            Config::KEY_KOUNT_MERCHANT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Merchant Id
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMerchantId()
    {
        return $this->getValue(
            Config::KEY_MERCHANT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Sdk Url
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSdkUrl()
    {
        return $this->getValue(
            Config::KEY_SDK_URL,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Check for fraud protection
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasFraudProtection()
    {
        return (bool) $this->getValue(
            Config::FRAUD_PROTECTION,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get fraud protection threshold
     *
     * @return float|null
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getFraudProtectionThreshold()
    {
        return $this->getValue(
            Config::FRAUD_PROTECTION_THRESHOLD,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isActive()
    {
        return (bool) $this->getValue(
            self::KEY_ACTIVE,
            $this->storeConfigResolver->getStoreId()
        );
    }

    /**
     * Get list of configured dynamic descriptors
     *
     * @return array
     */
    public function getDynamicDescriptors()
    {
        $values = [];
        foreach (self::$dynamicDescriptorKeys as $key) {
            $value = $this->getValue('descriptor_' . $key);
            if (!empty($value)) {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    /**
     * Get Merchant account ID
     *
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMerchantAccountId()
    {
        return $this->getValue(
            self::KEY_MERCHANT_ACCOUNT_ID,
            $this->storeConfigResolver->getStoreId()
        );
    }
}
