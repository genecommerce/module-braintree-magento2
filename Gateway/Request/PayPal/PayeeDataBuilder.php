<?php

namespace Magento\Braintree\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Config\Paypal\Config;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class PayeeDataBuilder
 * @package Magento\Braintree\Gateway\Request\PayPal
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class PayeeDataBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * PayeeDataBuilder constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $email = $this->config->getPayeeEmail();
        if ($email) {
            return [
                'options' => [
                    'paypal' => [
                        'payeeEmail' => $email
                    ]
                ]
            ];
        }

        return [];
    }
}
