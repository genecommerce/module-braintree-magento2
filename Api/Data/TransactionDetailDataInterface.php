<?php

namespace Magento\Braintree\Api\Data;

/**
 * Interface TransactionDetail
 * @package Magento\Braintree\Api\Data
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface TransactionDetailDataInterface
{
    const ENTITY_ID = 'entity_id';
    const ORDER_ID = 'order_id';
    const TRANSACTION_SOURCE = 'transaction_source';

    /**
     * @return int|null
     */
    public function getId();

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @return string
     */
    public function getTransactionSource(): string;

    /**
     * @param $id
     * @return self
     */
    public function setId($id);

    /**
     * @param int $orderId
     * @return self
     */
    public function setOrderId($orderId);

    /**
     * @param string $transactionSource
     * @return self
     */
    public function setTransactionSource($transactionSource);
}
