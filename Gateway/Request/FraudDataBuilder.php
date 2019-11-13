<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Helper\Formatter;

/**
 * Class FraudDataBuilder
 *
 * Add logical checks to enable/disable fraud checks.
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
     * @var State
     */
    private $state;

    /**
     * FraudDataBuilder constructor.
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param State $state
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        State $state
    ) {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        $threshold = $this->config->getFraudProtectionThreshold();
        $amount = $this->formatPrice($this->subjectReader->readAmount($buildSubject));

        if (($threshold && $amount >= $threshold) ||
            ($this->state->getAreaCode() === Area::AREA_ADMINHTML && $this->config->canSkipAdminFraudProtection())
        ) {
            return [
                'options' => [self::SKIP_ADVANCED_FRAUD_CHECKING => true]
            ];
        }

        return [
            'options' => [self::SKIP_ADVANCED_FRAUD_CHECKING => false]
        ];
    }
}
