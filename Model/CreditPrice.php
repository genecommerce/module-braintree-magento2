<?php

namespace Magento\Braintree\Model;

use Magento\Braintree\Api\Data\CreditPriceDataInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Braintree\Model\ResourceModel\CreditPrice as CreditPriceResource;

/**
 * Class CreditPrice
 * @package Magento\Braintree\Model
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class CreditPrice extends AbstractModel implements CreditPriceDataInterface
{
    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct() // @codingStandardsIgnoreLine
    {
        $this->_init(CreditPriceResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritdoc
     */
    public function getTerm()
    {
        return $this->getData(self::TERM);
    }

    /**
     * @inheritdoc
     */
    public function getMonthlyPayment()
    {
        return $this->getData(self::MONTHLY_PAYMENT);
    }

    /**
     * @inheritdoc
     */
    public function getInstalmentRate()
    {
        return $this->getData(self::INSTALMENT_RATE);
    }

    /**
     * @inheritdoc
     */
    public function getCostOfPurchase()
    {
        return $this->getData(self::COST_OF_PURCHASE);
    }

    /**
     * @inheritdoc
     */
    public function getTotalIncInterest()
    {
        return $this->getData(self::TOTAL_INC_INTEREST);
    }

    /**
     * @inheritdoc
     */
    public function setId($value)
    {
        return $this->setData(self::ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function setProductId($value)
    {
        return $this->setData(self::PRODUCT_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function setTerm($value)
    {
        return $this->setData(self::TERM, $value);
    }

    /**
     * @inheritdoc
     */
    public function setMonthlyPayment($value)
    {
        return $this->setData(self::MONTHLY_PAYMENT, $value);
    }

    /**
     * @inheritdoc
     */
    public function setInstalmentRate($value)
    {
        return $this->setData(self::INSTALMENT_RATE, $value);
    }

    /**
     * @inheritdoc
     */
    public function setCostOfPurchase($value)
    {
        return $this->setData(self::COST_OF_PURCHASE, $value);
    }

    /**
     * @inheritdoc
     */
    public function setTotalIncInterest($value)
    {
        return $this->setData(self::TOTAL_INC_INTEREST, $value);
    }
}
