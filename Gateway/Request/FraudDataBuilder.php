<?php

namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Helper\Formatter;

/**
 * Class FraudDataBuilder
 * @package Magento\Braintree\Gateway\Request
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class FraudDataBuilder implements BuilderInterface
{
    use Formatter;

    const SKIP_ADVANCED_FRAUD_CHECKING = 'skipAdvancedFraudChecking';

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var SubjectReader $subjectReader
     */
    private $subjectReader;

    /**
     * FraudDataBuilder constructor.
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * Skip advanced fraud checks if the order amount is equal to or greater than the defined threshold
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $threshold = $this->config->getFraudProtectionThreshold();
        $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));

        if ($threshold && $amount >= $threshold) {
            return [
                'options' => [self::SKIP_ADVANCED_FRAUD_CHECKING => true]
            ];
        }

        return [
            'options' => [self::SKIP_ADVANCED_FRAUD_CHECKING => false]
        ];
    }
}
