<?php
namespace Magento\Braintree\Model\GooglePay\Ui;

use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\GooglePay\Config;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Framework\View\Asset\Repository;

/**
 * Class ConfigProvider
 * @package Magento\Braintree\Model\GooglePay\Ui
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_googlepay';

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
     * @var string
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     * @param Config $config
     * @param BraintreeAdapter $adapter
     * @param Repository $assetRepo
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
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'braintree_googlepay' => [
                    'environment' => $this->getEnvironment(),
                    'clientToken' => $this->getClientToken(),
                    'merchantId' => $this->getMerchantId(),
                    'cardTypes' => $this->getAvailableCardTypes(),
                    'paymentMarkSrc' => $this->getPaymentMarkSrc()
                ]
            ]
        ];
    }

    /**
     * Generate a new client token if necessary
     * @return string
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
     * Get environment
     * @return string
     */
    public function getEnvironment()
    {
        return $this->config->getEnvironment();
    }

    /**
     * Get merchant name
     * @return string
     */
    public function getMerchantId()
    {
        return $this->config->getMerchantId();
    }

    /**
     * @return array
     */
    public function getAvailableCardTypes()
    {
        return $this->config->getAvailableCardTypes();
    }

    /**
     * Get the url to the payment mark image
     * @return mixed
     */
    public function getPaymentMarkSrc()
    {
        return $this->assetRepo->getUrl('Magento_Braintree::images/GooglePay_AcceptanceMark_WhiteShape_WithStroke_RGB_62x38pt@4x.png');
    }
}
