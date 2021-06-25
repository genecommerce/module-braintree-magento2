<?php

namespace Magento\Braintree\Gateway\Data;

use Magento\Payment\Gateway\Data\AddressAdapterInterface as MagentoAddressAdapterInterface;

/**
 * Interface AddressAdapterInterface
 * @api
 */
interface AddressAdapterInterface extends MagentoAddressAdapterInterface
{
    /**
     * Gets the street values
     *
     * @return string[]|null
     */
    public function getStreet();

}
