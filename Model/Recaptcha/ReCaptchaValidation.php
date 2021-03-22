<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Recaptcha;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Framework\Exception\InputException;

class ReCaptchaValidation
{
    /**
     * @var SubjectReader $subjectReader
     */
    private $subjectReader;

    /**
     * @var \Magento\Framework\ObjectManagerInterface $objectManager
     */
    private $objectManager;


    /**
     * @param SubjectReader $subjectReader
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     * @throws InputException
     */
    public function __construct(
        SubjectReader $subjectReader,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->subjectReader = $subjectReader;
        $this->objectManager = $objectmanager;
    }

    /**
     * @inheritdoc
     */
    public function validate($payment)
    {
        $paymentDO = $this->subjectReader->readPayment($payment);
        $payment = $paymentDO->getPayment();
        $token = $payment->getAdditionalInformation(
            DataAssignObserver::CAPTCHA_RESPONSE
        );
        if (empty($token)) {
            throw new CommandException(__('Can not resolve reCAPTCHA response.'));
        }

    }


}