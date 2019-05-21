<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Http\Client;

use Braintree\Result\Error;
use Braintree\Result\Successful;
use Magento\Braintree\Gateway\Request\PaymentDataBuilder;

/**
 * Class TransactionRefund
 * @package Magento\Braintree\Gateway\Http\Client
 */
class TransactionRefund extends AbstractTransaction
{
    /**
     * Process http request
     * @param array $data
     * @return Error|Successful
     */
    protected function process(array $data)
    {
        return $this->adapter->refund(
            $data['transaction_id'],
            $data[PaymentDataBuilder::AMOUNT]
        );
    }
}
