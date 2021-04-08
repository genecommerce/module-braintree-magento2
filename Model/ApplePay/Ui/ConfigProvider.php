<?php
namespace Magento\Braintree\Model\ApplePay\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\ApplePay\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ConfigProvider
 * @package Magento\Braintree\Model\ApplePay\Ui
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_applepay';

    const METHOD_KEY_ACTIVE = 'payment/braintree_applepay/active';

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
     * @var string
     */
    private $clientToken = '';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;

    /**
     * ConfigProvider constructor.
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
     * @inheritDoc
     */
    public function getConfig(): array
    {
        if (!$this->isActive()) {
            return [];
        }

        return [
            'payment' => [
                'braintree_applepay' => [
                    'clientToken' => $this->getClientToken(),
                    'merchantName' => $this->getMerchantName(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->scopeConfig->getValue(
            self::METHOD_KEY_ACTIVE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Generate a new client token if necessary
     *
     * @return string|null
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken()
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
     * Get merchant name
     *
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->config->getMerchantName();
    }

    /**
     * Get the url to the payment mark image
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/applepaymark.png');
    }
}
