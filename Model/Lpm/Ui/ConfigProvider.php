<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Lpm\Ui;

use Magento\Braintree\Gateway\Config\Lpm\Config;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const METHOD_CODE = 'braintree_local_payment';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * ConfigProvider constructor.
     *
     * @param Config $config
     * @param BraintreeAdapter $adapter
     */
    public function __construct(
        Config $config,
        BraintreeAdapter $adapter
    ) {
        $this->config = $config;
        $this->adapter = $adapter;
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
                    'isActive' => $this->config->isActive(),
                    'clientToken' => $this->getClientToken()
                ]
            ]
        ];
    }

    /**
     * @return mixed
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getClientToken()
    {
        if (empty($this->clientToken)) {
            $params = [];

            $merchantAccountId = $this->config->getMerchantAccountId();
            if (!empty($merchantAccountId)) {
                $params[PaymentDataBuilder::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
            }

            $this->clientToken = $this->adapter->generate($params);
        }

        return $this->clientToken;
    }
}
