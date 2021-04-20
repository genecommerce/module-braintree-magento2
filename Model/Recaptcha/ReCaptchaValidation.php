<?php
declare(strict_types=1);

namespace Magento\Braintree\Model\Recaptcha;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Framework\Exception\InputException;
use MSP\ReCaptcha\Api\ValidateInterface;
use MSP\ReCaptcha\Model\Config;
use Magento\Braintree\Gateway\Config\Config as GatewayConfig;

class ReCaptchaValidation
{
    /**
     * @var SubjectReader $subjectReader
     */
    private $subjectReader;

    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var GatewayConfig
     */
    private $gatewayConfig;

    /**
     * @param SubjectReader $subjectReader
     * @param ValidateInterface $validate
     * @param RemoteAddress $remoteAddress
     * @param Config $config
     * @param GatewayConfig $gatewayConfig
     * @throws InputException
     */
    public function __construct(
        SubjectReader $subjectReader,
        ValidateInterface $validate,
        RemoteAddress $remoteAddress,
        Config $config,
        GatewayConfig $gatewayConfig
    ) {
        $this->subjectReader = $subjectReader;
        $this->validate = $validate;
        $this->remoteAddress = $remoteAddress;
        $this->config = $config;
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * @inheritdoc
     */
    public function validate($payment)
    {
        $paymentDO = $this->subjectReader->readPayment($payment);
        $payment = $paymentDO->getPayment();
        if ($payment->getMethod() != 'braintree' || !$this->gatewayConfig->getCaptchaSettings()) {

            return;
        }
        $token = $payment->getAdditionalInformation(
            DataAssignObserver::CAPTCHA_RESPONSE
        );
        if (empty($token)) {
            throw new CommandException(__('Can not resolve reCAPTCHA response.'));
        }

        $remoteIp = $this->remoteAddress->getRemoteAddress();
        if (!$this->validate->validate($token, $remoteIp)) {
            throw new CommandException($this->config->getErrorDescription());
        }

    }
}