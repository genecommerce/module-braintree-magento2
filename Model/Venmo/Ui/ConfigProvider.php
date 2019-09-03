<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Venmo\Ui;

use Magento\Braintree\Gateway\Config\Config as BraintreeConfig;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_venmo';

    const MERCHANT_COUNTRY_CONFIG_VALUE = 'paypal/general/merchant_country';

    const ALLOWED_MERCHANT_COUNTRIES = ['US'];

    /**
     * @var BraintreeAdapter $adapter
     */
    private $adapter;
    /**
     * @var Repository $assetRepo
     */
    private $assetRepo;
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
     * @param Repository $assetRepo
     * @param BraintreeConfig $braintreeConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BraintreeAdapter $adapter,
        Repository $assetRepo,
        BraintreeConfig $braintreeConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->adapter = $adapter;
        $this->assetRepo = $assetRepo;
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
                    'isAllowed' => $this->isAllowed(),
                    'clientToken' => $this->getClientToken(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Venmo is (currently) for the US only.
     * Logic based on Merchant Country Location config option.
     *
     * @return bool
     */
    public function isAllowed(): bool
    {
        $merchantCountry = $this->scopeConfig->getValue(
            self::MERCHANT_COUNTRY_CONFIG_VALUE,
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
    public function getPaymentMarkSrc(): string
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/venmo_logo_blue.png');
    }
}
