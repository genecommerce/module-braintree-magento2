<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class Level23ProcessingDataBuilder
 */
class Level23ProcessingDataBuilder implements BuilderInterface
{
    const KEY_PURCHASE_ORDER_NUMBER = 'purchaseOrderNumber';

    const KEY_TAX_AMOUNT = 'taxAmount';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Level23ProcessingDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        $tax = 0;

        foreach ($order->getItems() as $item) {
            $tax += $item->getTaxAmount();
        }

        return [
            self::KEY_PURCHASE_ORDER_NUMBER => $order->getOrderIncrementId(),
            self::KEY_TAX_AMOUNT => $tax
        ];
    }
}
