<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Ach\Ui;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_ach_direct_debit';

    const CONFIG_MERCHANT_COUNTRY = 'paypal/general/merchant_country';

    const CONFIG_STORE_NAME = 'general/store_information/name';

    const CONFIG_STORE_URL = 'web/unsecure/base_url';

    const ALLOWED_MERCHANT_COUNTRIES = ['US'];

    /**
     * @var BraintreeAdapter $adapter
     */
    private $adapter;
    /**
     * @var BraintreeConfig $braintreeConfig
     */
    private $braintreeConfig;
    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;
    /**
     * @var string $clientToken
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     *
     * @param BraintreeAdapter $adapter
     * @param BraintreeConfig $braintreeConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BraintreeAdapter $adapter,
        BraintreeConfig $braintreeConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->adapter = $adapter;
        $this->braintreeConfig = $braintreeConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getConfig(): array
    {
        return [
            'payment' => [
                self::METHOD_CODE => [
                    'isActive' => $this->isActive(),
                    'clientToken' => $this->getClientToken(),
                    'storeName' => $this->getStoreName()
                ]
            ]
        ];
    }

    /**
     * ACH is for the US only.
     * Logic based on Merchant Country Location config option.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $merchantCountry = $this->scopeConfig->getValue(
            self::CONFIG_MERCHANT_COUNTRY,
            ScopeInterface::SCOPE_STORE
        );

        return in_array($merchantCountry, self::ALLOWED_MERCHANT_COUNTRIES, true);
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken(): string
    {
        if (empty($this->clientToken)) {
            $params = [];

            $merchantAccountId = $this->braintreeConfig->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapter->generate($params);
        }

        return $this->clientToken;
    }

    /**
     * @return string
     */
    public function getStoreName(): string
    {
        $storeName = $this->scopeConfig->getValue(
            self::CONFIG_STORE_NAME,
            ScopeInterface::SCOPE_STORE
        );

        // If store name is empty, use the base URL
        if (!$storeName) {
            $storeName = $this->scopeConfig->getValue(
                self::CONFIG_STORE_URL,
                ScopeInterface::SCOPE_STORE
            );
        }
        return $storeName;
    }
}
