<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use Braintree\ThreeDSecureInfo;
use Braintree\Transaction;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Class ThreeDSecureDetailsHandler
 * @package Magento\Braintree\Gateway\Response
 */
class ThreeDSecureDetailsHandler implements HandlerInterface
{
    const LIABILITY_SHIFTED = 'liabilityShifted';

    const LIABILITY_SHIFT_POSSIBLE = 'liabilityShiftPossible';

    const ECI_FLAG = 'eciFlag';

    const ECI_ACCEPTED_VALUES = [
        '00' => 'Failed',
        '01' => 'Attempted',
        '02' => 'Success',
        '07' => 'Failed',
        '06' => 'Attempted',
        '05' => 'Success'
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        ContextHelper::assertOrderPayment($payment);

        /** @var Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);

        if ($payment->hasAdditionalInformation(self::LIABILITY_SHIFTED)) {
            // remove 3d secure details for reorder
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFTED);
            $payment->unsAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE);
        }

        if (empty($transaction->threeDSecureInfo)) {
            return;
        }

        /** @var ThreeDSecureInfo $info */
        $info = $transaction->threeDSecureInfo;
        $payment->setAdditionalInformation(self::LIABILITY_SHIFTED, $info->liabilityShifted ? 'Yes' : 'No');
        $shiftPossible = $info->liabilityShiftPossible ? 'Yes' : 'No';
        $payment->setAdditionalInformation(self::LIABILITY_SHIFT_POSSIBLE, $shiftPossible);

        $eciFlag = $this->getEciFlagInformation($info->eciFlag);
        if ($eciFlag !== '') {
            $payment->setAdditionalInformation(self::ECI_FLAG, $eciFlag);
        }
    }

    /**
     * @param $eciFlagValue
     * @return mixed|string
     */
    public function getEciFlagInformation($eciFlagValue)
    {
        if ($eciFlagValue !== NULL && array_key_exists($eciFlagValue, self::ECI_ACCEPTED_VALUES)) {
            return self::ECI_ACCEPTED_VALUES[$eciFlagValue];
        }
        return '';
    }
}
