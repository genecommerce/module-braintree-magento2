<?php
declare(strict_types=1);

namespace Magento\Braintree\Model;

use Magento\Braintree\Api\Data\LocalPaymentInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Braintree\Model\ResourceModel\LocalPayment as LocalPaymentResource;

/**
 * Class LocalPayments
 */
class LocalPayment extends AbstractModel implements LocalPaymentInterface
{

    protected function _construct()
    {
        $this->_init(LocalPaymentResource::class);
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->getData(self::LPM_PAYMENT_ID);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function setPaymentId($id)
    {
        $this->setData(self::LPM_PAYMENT_ID, $id);
    }

    /**
     * @return mixed
     */
    public function getQuoteId()
    {
        return $this->getData(self::LPM_QUOTE_ID);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function setQuoteId($id)
    {
        $this->setData(self::LPM_QUOTE_ID, $id);
    }
}
