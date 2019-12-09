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

        foreach ($order->getItems() as $item) {

            // Skip configurable parent items and items with a base price of 0.
            if ($item->getParentItem() || 0.0 === $item->getPrice()) {
                continue;
            }

            // Regex to replace all unsupported characters.
            $filteredFields = preg_replace(
                '/[^a-zA-Z0-9\s\-.\']/',
                '',
                [
                    'name' => substr($item->getName(), 0, 35),
                    'unit_of_measure' => substr($item->getProductType(), 0, 12),
                    'sku' => substr($item->getSku(), 0, 12)
                ]
            );

            $lineItems[] = array_combine(
                self::LINE_ITEMS_ARRAY,
                [
                    $filteredFields['name'],
                    TransactionLineItem::DEBIT,
                    $this->numberToString($item->getQtyOrdered(), 2),
                    $this->numberToString($item->getBasePrice(), 2),
                    $filteredFields['unit_of_measure'],
                    $this->numberToString($item->getQtyOrdered() * $item->getBasePrice(), 2),
                    $item->getTaxAmount() === null ? '0.00' : $this->numberToString($item->getTaxAmount(), 2),
                    $item->getTaxAmount() === null ? '0.00' : $this->numberToString($item->getDiscountAmount(), 2),
                    $filteredFields['sku'],
                    $filteredFields['sku']
                ]
            );
        }

        $processingData = [
            self::KEY_PURCHASE_ORDER_NUMBER => $order->getOrderIncrementId(), // Level 2.
            self::KEY_TAX_AMT => $this->numberToString($order->getBaseTaxAmount(), 2), // Level 2.
            self::KEY_DISCOUNT_AMT => $this->numberToString(abs($order->getBaseDiscountAmount()), 2), // Level 3.
            self::KEY_LINE_ITEMS => $lineItems, // Level 3.
        ];

        // Only add these shipping related details if a shipping address is present.
        if ($order->getShippingAddress()) {
            $storePostalCode = $this->scopeConfig->getValue(
                'general/store_information/postcode',
                ScopeInterface::SCOPE_STORE
            );

            $address = $order->getShippingAddress();
            // use Magento's Alpha2 code to get the Alpha3 code.
            $addressData = $this->iso3166->alpha2($address->getCountryId());

            $processingData[self::KEY_SHIPPING_AMT] = $this->numberToString($payment->getShippingAmount(), 2); // Level 3.
            $processingData[self::KEY_SHIPS_FROM_POSTAL_CODE] = $storePostalCode; // Level 3.
            $processingData[self::KEY_SHIPPING] = [ // Level 3.
                self::KEY_COUNTRY_CODE_ALPHA_3 => $addressData['alpha3']
            ];
        }

        return $processingData;
    }

    /**
     * @param float $num
     * @param int $precision
     * @return string
     */
    private function numberToString(float $num, int $precision): string
    {
        return (string) round($num, $precision);
    }
}
