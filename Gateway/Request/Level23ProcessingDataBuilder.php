<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Braintree\TransactionLineItem;
use League\ISO3166\ISO3166;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class Level23ProcessingDataBuilder
 */
class Level23ProcessingDataBuilder implements BuilderInterface
{
    const KEY_PURCHASE_ORDER_NUMBER = 'purchaseOrderNumber';
    const KEY_TAX_AMT = 'taxAmount';
    const KEY_SHIPPING_AMT = 'shippingAmount';
    const KEY_DISCOUNT_AMT = 'discountAmount';
    const KEY_SHIPPING = 'shipping';
    const KEY_COUNTRY_CODE_ALPHA_3 = 'countryCodeAlpha3';
    const KEY_LINE_ITEMS = 'lineItems';
    const LINE_ITEMS_ARRAY = [
        'name',
        'kind',
        'quantity',
        'unitAmount',
        'unitOfMeasure',
        'totalAmount',
        'taxAmount',
        'discountAmount',
        'productCode',
        'commodityCode'
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;
    /**
     * @var ISO3166
     */
    private $iso3166;

    /**
     * Level23ProcessingDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ISO3166 $iso3166
     */
    public function __construct(SubjectReader $subjectReader, ISO3166 $iso3166)
    {
        $this->subjectReader = $subjectReader;
        $this->iso3166 = $iso3166;
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
        $billingAddress = $order->getBillingAddress();
        $addressData = $this->iso3166->alpha2($billingAddress->getCountryId());

        $tax = 0;
        $lineItems = [];

        foreach ($order->getItems() as $item) {
            $tax += $item->getTaxAmount();

            $lineItems[] = array_combine(
                self::LINE_ITEMS_ARRAY,
                [
                    $item->getName(),
                    TransactionLineItem::DEBIT,
                    $item->getQtyOrdered(),
                    $item->getPrice(),
                    $item->getProductType(),
                    $item->getQtyOrdered() * $item->getPrice(),
                    $item->getTaxAmount(),
                    $item->getDiscountAmount(),
                    $item->getSku(),
                    $item->getSku()
                ]
            );
        }

        return [
            self::KEY_PURCHASE_ORDER_NUMBER => $order->getOrderIncrementId(),
            self::KEY_TAX_AMT => $tax,
            self::KEY_LINE_ITEMS => $lineItems,
            self::KEY_SHIPPING => [
                self::KEY_COUNTRY_CODE_ALPHA_3 => $addressData['alpha3']
            ]
        ];
    }
}
