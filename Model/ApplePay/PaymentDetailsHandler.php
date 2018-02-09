<?php

namespace Magento\Braintree\Model\ApplePay;

/**
 * Class PaymentDetailsHandler
 * @package Magento\Braintree\Model\ApplePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class PaymentDetailsHandler extends \Magento\Braintree\Gateway\Response\PaymentDetailsHandler
{
    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];
}
