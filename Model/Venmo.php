<?php

namespace Magento\Braintree\Model;

use Magento\Payment\Model\Method\AbstractMethod;

class Venmo extends AbstractMethod
{
    public function getCode()
    {
        return 'braintree_venmo';
    }
}
