<?php
declare(strict_types=1);

namespace Magento\Braintree\Gateway\Request;

use Braintree\TransactionLineItem;
use League\ISO3166\ISO3166;
use Magento\Braintree\Gateway\Data\Order\OrderAdapter;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Level23ProcessingDataBuilder
 */
class Level23ProcessingDataBuilder implements BuilderInterface
{
    const KEY_PURCHASE_ORDER_NUMBER = 'purchaseOrderNumber';
    const KEY_TAX_AMT = 'taxAmount';
    const KEY_SHIPPING_AMT = 'shippingAmount';
    const KEY_DISCOUNT_AMT = 'discountAmount';
    const KEY_SHIPS_FROM_POSTAL_CODE = 'shipsFromPostalCode';
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
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var ISO3166
     */
    private $iso3166;

    /**
     * Level23ProcessingDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param ScopeConfigInterface $scopeConfig
     * @param ISO3166 $iso3166
     */
    public function __construct(
        SubjectReader $subjectReader,
        ScopeConfigInterface $scopeConfig,
        ISO3166 $iso3166
    ) {
        $this->subjectReader = $subjectReader;
        $this->scopeConfig = $scopeConfig;
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
        $tax = 0;
        $lineItems = [];

        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();
        
        /**
         * Override in di.xml so we can add extra public methods.
         * In this instance, so we can eventually get the discount amount.
         * @var OrderAdapter $order
         */
        $order = $paymentDO->getOrder();

        $billingAddress = $order->getBillingAddress();

        // use Magento's Alpha2 code to get the Alpha3 code.
        $addressData = $this->iso3166->alpha2($billingAddress->getCountryId());

        foreach ($order->getItems() as $item) {
            /** @var OrderItemInterface $item */
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

        $storePostalCode = $this->scopeConfig->getValue(
            'general/store_information/postcode',
            ScopeInterface::SCOPE_STORE
        );

        return [
            self::KEY_PURCHASE_ORDER_NUMBER => $order->getOrderIncrementId(), // Level 2.
            self::KEY_TAX_AMT => $tax, // Level 2.
            self::KEY_SHIPPING_AMT => $payment->getShippingAmount(), // Level 3.
            self::KEY_DISCOUNT_AMT => abs($order->getBaseDiscountAmount()), // Level 3.
            self::KEY_SHIPS_FROM_POSTAL_CODE => $storePostalCode, // Level 3.
            self::KEY_LINE_ITEMS => $lineItems, // Level 3.
            self::KEY_SHIPPING => [ // Level 3.
                self::KEY_COUNTRY_CODE_ALPHA_3 => $addressData['alpha3']
            ]
        ];
    }
}
