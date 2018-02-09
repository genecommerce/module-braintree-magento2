<?php

namespace Magento\Braintree\Api\Data;

/**
 * Interface CreditPriceDataInterface
 * @package Magento\Braintree\Api\Data
 * @api
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
interface CreditPriceDataInterface
{
    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const TERM = 'term';
    const MONTHLY_PAYMENT = 'monthly_payment';
    const INSTALMENT_RATE = 'instalment_rate';
    const COST_OF_PURCHASE = 'cost_of_purchase';
    const TOTAL_INC_INTEREST = 'total_inc_interest';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $value
     * @return CreditPriceDataInterface
     */
    public function setId($value);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $value
     * @return CreditPriceDataInterface
     */
    public function setProductId($value);

    /**
     * @return int
     */
    public function getTerm();

    /**
     * @param int $value
     * @return CreditPriceDataInterface
     */
    public function setTerm($value);

    /**
     * @return float
     */
    public function getMonthlyPayment();

    /**
     * @param float $value
     * @return CreditPriceDataInterface
     */
    public function setMonthlyPayment($value);

    /**
     * @return float
     */
    public function getInstalmentRate();

    /**
     * @param float $value
     * @return CreditPriceDataInterface
     */
    public function setInstalmentRate($value);

    /**
     * @return float
     */
    public function getCostOfPurchase();

    /**
     * @param float $value
     * @return CreditPriceDataInterface
     */
    public function setCostOfPurchase($value);

    /**
     * @return float
     */
    public function getTotalIncInterest();

    /**
     * @param float $value
     * @return CreditPriceDataInterface
     */
    public function setTotalIncInterest($value);
}
