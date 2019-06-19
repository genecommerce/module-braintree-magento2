<?php

namespace Magento\Braintree\Api;

use Magento\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Framework\DataObject;

/**
 * Interface CreditPricesInterface
 * @package Magento\Braintree\Api
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface CreditPriceRepositoryInterface
{
    /**
     * @param \Magento\Braintree\Api\Data\CreditPriceDataInterface $entity
     * @return \Magento\Braintree\Api\Data\CreditPriceDataInterface
     */
    public function save(CreditPriceDataInterface $entity): CreditPriceDataInterface;

    /**
     * @param int $productId
     * @return \Magento\Braintree\Api\Data\CreditPriceDataInterface
     */
    public function getByProductId($productId);

    /**
     * @param $productId
     * @return \Magento\Braintree\Api\Data\CreditPriceDataInterface|\Magento\Framework\DataObject
     */
    public function getCheapestByProductId($productId);

    /**
     * @param int $productId
     * @return \Magento\Braintree\Api\Data\CreditPriceDataInterface[]
     */
    public function deleteByProductId($productId);
}
