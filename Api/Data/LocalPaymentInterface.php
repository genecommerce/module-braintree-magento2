<?php
declare(strict_types=1);

namespace Magento\Braintree\Api\Data;

/**
 * Interface LocalPaymentInterface
 */
interface LocalPaymentInterface
{
    const LPM_ID = 'id';
    const LPM_PAYMENT_ID = 'payment_id';
    const LPM_QUOTE_ID = 'quote_id';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getPaymentId();

    /**
     * @param $id
     * @return mixed
     */
    public function setPaymentId($id);

    /**
     * @return mixed
     */
    public function getQuoteId();

    /**
     * @param $id
     * @return mixed
     */
    public function setQuoteId($id);
}
