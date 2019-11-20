<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Lpm;

/**
 * Class PaymentDetailsHandler
 */
class PaymentDetailsHandler extends \Magento\Braintree\Gateway\Response\PaymentDetailsHandler
{
    /**
     * List of additional details
     * @var array $additionalInformationMapping
     */
    protected $additionalInformationMapping = [
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT
    ];
}
