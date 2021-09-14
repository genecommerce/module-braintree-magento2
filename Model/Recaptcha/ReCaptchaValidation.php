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
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * Class ReCaptchaValidation
 * @package Magento\Braintree\Model\Recaptcha
 */
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
     * @var State
     */
    protected $state;

    /**
     * @param SubjectReader $subjectReader
     * @param ValidateInterface $validate
     * @param RemoteAddress $remoteAddress
     * @param Config $config
     * @param GatewayConfig $gatewayConfig
     * @param State $state
     * @throws InputException
     */
    public function __construct(
        SubjectReader $subjectReader,
        ValidateInterface $validate,
        RemoteAddress $remoteAddress,
        Config $config,
        GatewayConfig $gatewayConfig,
        State $state
    ) {
        $this->subjectReader = $subjectReader;
        $this->validate = $validate;
        $this->remoteAddress = $remoteAddress;
        $this->config = $config;
        $this->gatewayConfig = $gatewayConfig;
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function validate($payment)
    {
        $paymentDO = $this->subjectReader->readPayment($payment);
        $payment = $paymentDO->getPayment();

        $token = $payment->getAdditionalInformation(DataAssignObserver::CAPTCHA_RESPONSE);

        if (
            in_array($this->state->getAreaCode(), [Area::AREA_ADMINHTML, Area::AREA_CRONTAB])
            || $payment->getMethod() !== 'braintree'
            || !$this->gatewayConfig->getCaptchaSettings()
            || $payment->getOrder()->getCustomerId()
        ) {
            return;
        }

        $remoteIp = $this->remoteAddress->getRemoteAddress();
        if (!$this->validate->validate($token, $remoteIp)) {
            throw new CommandException(__(
                'reCAPTCHA validation error: %1',
                $this->config->getErrorDescription()
            ));
        }
    }
}
