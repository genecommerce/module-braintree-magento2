<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class ThreeDSecureDataBuilder
 */
class ThreeDSecureDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $result = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));

        if ($this->is3DSecureEnabled($paymentDO->getOrder(), $amount)) {
            $result['options'][Config::CODE_3DSECURE] = ['required' => true];
        }
        return $result;
    }

    /**
     * Check if 3d secure is enabled
     * @param OrderAdapterInterface $order
     * @param float $amount
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    protected function is3DSecureEnabled(OrderAdapterInterface $order, $amount): bool
    {
        if (!$this->config->isVerify3DSecure() || $amount < $this->config->getThresholdAmount()) {
            return false;
        }

        $billingAddress = $order->getBillingAddress();
        $specificCounties = $this->config->get3DSecureSpecificCountries();

        return !(!empty($specificCounties) && !in_array($billingAddress->getCountryId(), $specificCounties));
    }
}
