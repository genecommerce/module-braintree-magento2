<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Braintree\Gateway\Helper\SubjectReader;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * ShippingAddress block name
     */
    const SHIPPING_ADDRESS = 'shipping';

    /**
     * BillingAddress block name
     */
    const BILLING_ADDRESS = 'billing';

    /**
     * The first name value must be less than or equal to 255 characters.
     */
    const FIRST_NAME = 'firstName';

    /**
     * The last name value must be less than or equal to 255 characters.
     */
    const LAST_NAME = 'lastName';

    /**
     * The customer’s company. 255 character maximum.
     */
    const COMPANY = 'company';

    /**
     * The street address. Maximum 255 characters, and must contain at least 1 digit.
     * Required when AVS rules are configured to require street address.
     */
    const STREET_ADDRESS = 'streetAddress';

    /**
     * The extended address information—such as apartment or suite number. 255 character maximum.
     */
    const EXTENDED_ADDRESS = 'extendedAddress';

    /**
     * The locality/city. 255 character maximum.
     */
    const LOCALITY = 'locality';

    /**
     * The state or province. For PayPal addresses, the region must be a 2-letter abbreviation;
     * for all other payment methods, it must be less than or equal to 255 characters.
     */
    const REGION = 'region';

    /**
     * The postal code. Postal code must be a string of 5 or 9 alphanumeric digits,
     * optionally separated by a dash or a space. Spaces, hyphens,
     * and all other special characters are ignored.
     */
    const POSTAL_CODE = 'postalCode';

    /**
     * The ISO 3166-1 alpha-2 country code specified in an address.
     * The gateway only accepts specific alpha-2 values.
     *
     * @link https://developers.braintreepayments.com/reference/general/countries/php#list-of-countries
     */
    const COUNTRY_CODE = 'countryCodeAlpha2';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $result = [];

        $billingAddress = $order->getBillingAddress();
        if ($billingAddress) {
            $street = $billingAddress->getStreet();
            $streetAddress = isset($street[0]) ? $street[0] : '';
            $streetAddress1 = isset($street[1]) ? $street[1] : '';
            $streetAddress2 = isset($street[2]) ? $street[2] : '';
            $streetAddress3 = isset($street[3]) ? $street[3] : '';

            $extendedAddress = $streetAddress1;
            if (!empty($streetAddress1)) {
                if (!empty($streetAddress2)) {
                    $extendedAddress = $streetAddress1 . ", " . $streetAddress2;
                }
                if (!empty($streetAddress3)) {
                    $extendedAddress = $streetAddress1 . ", " . $streetAddress2 . ", " . $streetAddress3;
                }
            }

            $result[self::BILLING_ADDRESS] = [
                self::FIRST_NAME => $billingAddress->getFirstname(),
                self::LAST_NAME => $billingAddress->getLastname(),
                self::COMPANY => $billingAddress->getCompany(),
                self::STREET_ADDRESS => $streetAddress,
                self::EXTENDED_ADDRESS => $extendedAddress,
                self::LOCALITY => $billingAddress->getCity(),
                self::REGION => $billingAddress->getRegionCode(),
                self::POSTAL_CODE => $billingAddress->getPostcode(),
                self::COUNTRY_CODE => $billingAddress->getCountryId()
            ];
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $street = $shippingAddress->getStreet();
            $streetAddress = isset($street[0]) ? $street[0] : '';
            $streetAddress1 = isset($street[1]) ? $street[1] : '';
            $streetAddress2 = isset($street[2]) ? $street[2] : '';
            $streetAddress3 = isset($street[3]) ? $street[3] : '';

            $extendedAddress = $streetAddress1;
            if (!empty($streetAddress1)) {
                if (!empty($streetAddress2)) {
                    $extendedAddress = $streetAddress1 . ", " . $streetAddress2;
                }
                if (!empty($streetAddress3)) {
                    $extendedAddress = $streetAddress1 . ", " . $streetAddress2 . ", " . $streetAddress3;
                }
            }

            $result[self::SHIPPING_ADDRESS] = [
                self::FIRST_NAME => $shippingAddress->getFirstname(),
                self::LAST_NAME => $shippingAddress->getLastname(),
                self::COMPANY => $shippingAddress->getCompany(),
                self::STREET_ADDRESS => $streetAddress,
                self::EXTENDED_ADDRESS => $extendedAddress,
                self::LOCALITY => $shippingAddress->getCity(),
                self::REGION => $shippingAddress->getRegionCode(),
                self::POSTAL_CODE => $shippingAddress->getPostcode(),
                self::COUNTRY_CODE => $shippingAddress->getCountryId()
            ];
        }

        return $result;
    }
}
