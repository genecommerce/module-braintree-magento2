<?php
namespace Magento\Braintree\Model\ApplePay\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\ApplePay\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 * @package Magento\Braintree\Model\ApplePay\Ui
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_applepay';

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
     * ConfigProvider constructor.
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @param Repository $assetRepo
     * @param \Magento\Braintree\Gateway\Config\Config $braintreeConfig
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter,
        Repository $assetRepo,
        \Magento\Braintree\Gateway\Config\Config $braintreeConfig
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
        $this->assetRepo = $assetRepo;
        $this->braintreeConfig = $braintreeConfig;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
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
