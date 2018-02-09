<?php

namespace Magento\Braintree\Block\Adminhtml\Virtual;

/**
 * Class Script
 * @package Magento\Braintree\Block\Adminhtml\Virtual
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Script extends \Magento\Braintree\Block\Payment
{
    public function getMethodCode()
    {
        return 'braintree';
    }

    public function isVaultEnabled()
    {
        return false;
    }

    public function hasVerification()
    {
        return true;
    }
}
