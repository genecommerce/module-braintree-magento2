<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block;

use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Payment
 *
 * @api
 * @since 100.1.0
 */
class Payment extends Template
{
    /**
     * @var ConfigProviderInterface
     */
    private $config;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ConfigProviderInterface $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProviderInterface $config,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
    }

    /**
     * Get Payment Config
     *
     * @return string
     */
    public function getPaymentConfig(): string
    {
        $config = $this->config->getConfig();
        if (isset($config['payment'])) {
            $payment = $config['payment'];
            $config = $payment[$this->getCode()];
        }
        $config['code'] = $this->getCode();

        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return ConfigProvider::CODE;
    }
}
