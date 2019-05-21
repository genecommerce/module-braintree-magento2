<?php

namespace Magento\Braintree\Api;

use Magento\Braintree\Api\Data\AuthDataInterface;

/**
 * Interface AuthInterface
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface AuthInterface
{
    /**
     * Returns details required to be able to submit a payment with apple pay.
     * @return AuthDataInterface
     */
    public function get(): AuthDataInterface;
}
