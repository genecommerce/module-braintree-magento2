<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\PaymentMethodNonce;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adminhtml\Source\Environment;
use Magento\Braintree\Model\StoreConfigResolver;

/**
 * Class BraintreeAdapter
 * @codeCoverageIgnore
 */
class BraintreeAdapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreConfigResolver
     */
    private $storeConfigResolver;

    /**
     * BraintreeAdapter constructor.
     *
     * @param Config              $config              Braintree configurator
     * @param StoreConfigResolver $storeConfigResolver StoreId resolver model
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(
        Config $config,
        StoreConfigResolver $storeConfigResolver
    ) {
        $this->config = $config;
        $this->storeConfigResolver = $storeConfigResolver;
        $this->initCredentials();
    }

    /**
     * Initializes credentials.
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function initCredentials()
    {
        $storeId = $this->storeConfigResolver->getStoreId();
        $environmentIdentifier = $this->config
            ->getValue(Config::KEY_ENVIRONMENT, $storeId);

        $this->environment(Environment::ENVIRONMENT_SANDBOX);

        if ($environmentIdentifier == Environment::ENVIRONMENT_PRODUCTION) {
            $this->environment(Environment::ENVIRONMENT_PRODUCTION);
        }

        $this->merchantId(
            $this->config->getValue(Config::KEY_MERCHANT_ID, $storeId)
        );
        $this->publicKey(
            $this->config->getValue(Config::KEY_PUBLIC_KEY, $storeId)
        );
        $this->privateKey(
            $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId)
        );
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function environment($value = null)
    {
        return Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
     * @param array $params
     * @return \Braintree\Result\Successful|\Braintree\Result\Error|null
     */
    public function generate(array $params = [])
    {
        try {
            return ClientToken::generate($params);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $token
     * @return \Braintree\CreditCard|null
     */
    public function find($token)
    {
        try {
            return CreditCard::find($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return \Braintree\ResourceCollection
     */
    public function search(array $filters)
    {
        return Transaction::search($filters);
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function createNonce($token)
    {
        return PaymentMethodNonce::create($token);
    }

    /**
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function sale(array $attributes)
    {
        $r= Transaction::sale($attributes);
        return $r;
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function void($transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function refund($transactionId, $amount = null)
    {
        return Transaction::refund($transactionId, $amount);
    }

    /**
     * Clone original transaction
     * @param string $transactionId
     * @param array $attributes
     * @return mixed
     */
    public function cloneTransaction($transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }
}
