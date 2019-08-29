<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Venmo\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Venmo\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 *
 * @package Magento\Braintree\Model\Venmo\Ui
 * @author Paul Canning <paul.canning@gene.co.uk>
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_venmo';
    /**
     * @var Config
     */
    private $config;
    /**
     * @var BraintreeAdapter
     */
    private $adapter;
    /**
     * @var Repository
     */
    private $assetRepo;
    /**
     * @var \Magento\Braintree\Gateway\Config\Config
     */
    private $braintreeConfig;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @param Repository $assetRepo
     * @param \Magento\Braintree\Gateway\Config\Config $braintreeConfig
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter,
        Repository $assetRepo,
        \Magento\Braintree\Gateway\Config\Config $braintreeConfig,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
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
                    'environment' => $this->getEnvironment(),
                    'clientToken' => $this->getClientToken(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Venmo is for the US only.
     * Logic based on Merchant Country Location config option.
     * @return bool
     */
    public function isAllowed(): bool
    {
        $merchantCountry = $this->scopeConfig->getValue(
            'paypal/general/merchant_country',
            ScopeInterface::SCOPE_STORE
        );

        return 'US' === $merchantCountry;
    }

    /**
     * @return string
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getEnvironment(): string
    {
        return $this->config->getEnvironment();
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
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/venmo_logo_blue.png');
    }
}
