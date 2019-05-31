<?php

namespace Magento\Braintree\Model\ApplePay;

/**
 * Class Config
 * @package Magento\Braintree\Model\ApplePay
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * Get merchant name to display
     *
     * @return string
     */
    public function getMerchantName(): string
    {
        return $this->getValue('merchant_name');
    }
}
