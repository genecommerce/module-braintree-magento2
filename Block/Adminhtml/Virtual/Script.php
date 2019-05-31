<?php

namespace Magento\Braintree\Block\Adminhtml\Virtual;

use Magento\Braintree\Block\Payment;

/**
 * Class Script
 * @package Magento\Braintree\Block\Adminhtml\Virtual
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Script extends Payment
{
    /**
     * @return string
     */
    public function getMethodCode(): string
    {
        return 'braintree';
    }

    /**
     * @return bool
     */
    public function isVaultEnabled(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    public function hasVerification(): bool
    {
        return true;
    }
}
