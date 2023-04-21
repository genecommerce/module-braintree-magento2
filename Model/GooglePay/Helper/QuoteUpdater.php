<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Model\GooglePay\Helper;

use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Braintree\Model\GooglePay\Ui\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Braintree\Model\Paypal\Helper\AbstractHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Directory\Model\Region;

class QuoteUpdater extends AbstractHelper
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Region
     */
    private Region $region;

    /**
     * QuoteUpdater constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        Region $region
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->region = $region;
    }

    /**
     * Execute operation
     *
     * @param string $nonce
     * @param string $deviceData
     * @param array $details
     * @param Quote $quote
     * @return void
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function execute(string $nonce, string $deviceData, array $details, Quote $quote)
    {
        if (empty($nonce) || empty($details)) {
            throw new InvalidArgumentException('The "nonce" and "details" fields do not exist');
        }

        $payment = $quote->getPayment();
        $payment->setMethod(ConfigProvider::METHOD_CODE);
        $payment->setAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE, $nonce);
        $payment->setAdditionalInformation(DataAssignObserver::DEVICE_DATA, $deviceData);
        $this->updateQuote($quote, $details);
    }

    /**
     * Update quote data
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateQuote(Quote $quote, array $details)
    {
        $this->eventManager->dispatch('braintree_googlepay_update_quote_before', [
            'quote' => $quote,
            'googlepay_response' => $details
        ]);

        $quote->setMayEditShippingAddress(false);
        $quote->setMayEditShippingMethod(true);

        $this->updateQuoteAddress($quote, $details);
        $this->disabledQuoteAddressValidation($quote);

        $quote->collectTotals();

        /**
         * Unset shipping assignment to prevent from saving / applying outdated data
         * @see \Magento\Quote\Model\QuoteRepository\SaveHandler::processShippingAssignment
         */
        if ($quote->getExtensionAttributes()) {
            $quote->getExtensionAttributes()->setShippingAssignments(null);
        }

        $this->quoteRepository->save($quote);

        $this->eventManager->dispatch('braintree_googlepay_update_quote_after', [
            'quote' => $quote,
            'googlepay_response' => $details
        ]);
    }

    /**
     * Update quote address
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateQuoteAddress(Quote $quote, array $details)
    {
        if (!$quote->getIsVirtual()) {
            $this->updateShippingAddress($quote, $details);
        }

        $this->updateBillingAddress($quote, $details);
    }

    /**
     * Update shipping address
     * (PayPal doesn't provide detailed shipping info: prefix, suffix)
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateShippingAddress(Quote $quote, array $details)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true);
        $this->updateAddressData($shippingAddress, $details['shippingAddress']);

        // PayPal's address supposes not saving against customer account
        $shippingAddress->setSaveInAddressBook(false);
        $shippingAddress->setSameAsBilling(false);
        $shippingAddress->unsCustomerAddressId();
    }

    /**
     * Update billing address
     *
     * @param Quote $quote
     * @param array $details
     * @return void
     */
    private function updateBillingAddress(Quote $quote, array $details)
    {
        $billingAddress = $quote->getBillingAddress();
        $this->updateAddressData($billingAddress, $details['billingAddress']);

        $billingAddress->setSaveInAddressBook(false);
        $billingAddress->setSameAsBilling(false);
        $billingAddress->unsCustomerAddressId();
    }

    /**
     * Sets address data from exported address
     *
     * @param Address $address
     * @param array $addressData
     * @return void
     */
    private function updateAddressData(Address $address, array $addressData)
    {
        $street = $addressData['streetAddress'];

        if (isset($addressData['extendedAddress'])) {
            $street = $street . ' ' . $addressData['extendedAddress'];
        }
        
        $regionFromAddressData = $this->region->loadByCode($addressData['region'], $addressData['countryCodeAlpha2']);

        $name = explode(' ', $addressData['name'], 2);

        $address->setEmail($addressData['email']);
        $address->setFirstname($name[0]);
        $address->setLastname($name[1] ?? '');

        $address->setStreet($street);
        $address->setCity($addressData['locality']);
        $address->setRegionCode($addressData['region']);
        $address->setCountryId($addressData['countryCodeAlpha2']);
        if( $regionFromAddressData ) {
            $address->setRegion( $regionFromAddressData->getName() );
            $address->setRegionId( $regionFromAddressData->getId() );
        }
        $address->setPostcode($addressData['postalCode']);

        if (!empty($addressData['telephone'])) {
            $address->setTelephone($addressData['telephone']);
        }

        $address->setSaveInAddressBook(false);
        $address->setSameAsBilling(false);
        $address->setCustomerAddressId(null);
    }
}
