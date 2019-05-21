<?php

namespace Magento\Braintree\Api;

use Magento\Braintree\Api\Data\CreditPriceDataInterface;

/**
 * Interface CreditPricesInterface
 * @package Magento\Braintree\Api
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface CreditPriceRepositoryInterface
{
    /**
     * @param CreditPriceDataInterface $entity
     * @return CreditPriceDataInterface
     */
    public function save(CreditPriceDataInterface $entity): CreditPriceDataInterface;

    /**
     * @param int $productId
     * @return CreditPriceDataInterface
     */
    public function getByProductId($productId): CreditPriceDataInterface;

    /**
     * @param $productId
     * @return Data\CreditPriceDataInterface
     */
    public function getCheapestByProductId($productId): CreditPriceDataInterface;

    /**
     * @param int $productId
     * @return mixed
     */
    public function deleteByProductId($productId);
}
