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
    public function save(CreditPriceDataInterface $entity);

    /**
     * @param int $productId
     * @return CreditPriceDataInterface
     */
    public function getByProductId($productId);

    /**
     * @param $productId
     * @return Data\CreditPriceDataInterface
     */
    public function getCheapestByProductId($productId);

    /**
     * @param int $productId
     * @return mixed
     */
    public function deleteByProductId($productId);
}
