<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Block\GooglePay\Checkout;

use Magento\Paypal\Block\Express;

/**
 * Class Review
 */
class Review extends Express\Review
{
    /**
     * @var string
     */
    protected $_controllerPath = 'braintree/googlepay'; // @codingStandardsIgnoreLine
}
