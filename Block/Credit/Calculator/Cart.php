<?php

namespace Magento\Braintree\Block\Credit\Calculator;

use Magento\Framework\View\Element\Template;
use Magento\Braintree\Gateway\Config\PayPalCredit\Config as PayPalCreditConfig;

/**
 * Class Cart
 * @package Magento\Braintree\Block\Credit\Calculator
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Cart extends Template
{
    /**
     * @var PayPalCreditConfig
     */
    protected $config;

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param PayPalCreditConfig $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PayPalCreditConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml() // @codingStandardsIgnoreLine
    {
        if ($this->config->isCalculatorEnabled()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->config->getMerchantName();
    }
}
