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
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getTransactionSource();

    /**
     * @param $id
     * @return object
     */
    public function setId($id);

    /**
     * @param $orderId int
     * @return object
     */
    public function setOrderId($orderId);

    /**
     * @param $transactionSource string
     * @return object
     */
    public function setTransactionSource($transactionSource);
}
