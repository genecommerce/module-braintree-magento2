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
     * @param int $websiteId
     * @return CreditPriceDataInterface
     */
    public function getByProductId($productId, $websiteId = 0);

    /**
     * @param $productId
     * @param int $websiteId
     * @return Data\CreditPriceDataInterface
     */
    public function getCheapestByProductId($productId, $websiteId = 0);

    /**
     * @param int $productId
     * @param int $websiteId
     * @return mixed
     */
    public function deleteByProductId($productId, $websiteId = 0);
}
