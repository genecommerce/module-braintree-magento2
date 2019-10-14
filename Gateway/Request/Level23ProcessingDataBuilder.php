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
        return [];
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
