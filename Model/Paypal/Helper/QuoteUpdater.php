<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Paypal\Helper;

use InvalidArgumentException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Framework\Event\ManagerInterface;

/**
 * Class QuoteUpdater
 */
class QuoteUpdater extends AbstractHelper
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address
     */
    private $addressFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * Constructor
     *
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     * @param ManagerInterface $eventManager
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address $addressFactory
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $quoteRepository,
        ManagerInterface $eventManager,
        \Magento\Quote\Model\ResourceModel\Quote\Address $addressFactory
    ) {
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->eventManager = $eventManager;
        $this->addressFactory = $addressFactory;
    }

    /**
     * Execute operation
     *
     * @param string $nonce
     * @param array $details
     * @param Quote $quote
     * @return void
     * @throws InvalidArgumentException
     * @throws LocalizedException
     */
    public function execute($nonce, array $details, Quote $quote)
    {
        if (empty($nonce) || empty($details)) {
            throw new InvalidArgumentException('The "nonce" and "details" fields do not exist.');
        }

        $payment = $quote->getPayment();
        $payment->setMethod(ConfigProvider::PAYPAL_CODE);
        $payment->setAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE, $nonce);
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
        $this->eventManager->dispatch('braintree_paypal_update_quote_before', [
            'quote' => $quote,
            'paypal_response' => $details
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
        $this->cleanUpAddress($quote);

        $this->eventManager->dispatch('braintree_paypal_update_quote_after', [
            'quote' => $quote,
            'paypal_response' => $details
        ]);
    }

    /**
     * @param Quote $quote
     */
    private function cleanUpAddress(Quote $quote)
    {
        $tableName = $this->addressFactory->getConnection()->getTableName('quote_address');
        $this->addressFactory->getConnection()->delete(
            $tableName,
            'quote_id = ' . (int) $quote->getId() . ' AND email IS NULL'
        );
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
        $shippingAddress->setLastname($details['lastName']);
        $shippingAddress->setFirstname($details['firstName']);
        $shippingAddress->setEmail($details['email']);

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
        $billingAddress->setFirstname($details['firstName']);
        $billingAddress->setLastname($details['lastName']);
        $billingAddress->setEmail($details['email']);

        if ($this->config->isRequiredBillingAddress()) {
            $this->updateAddressData($billingAddress, $details['billingAddress']);

            if (!empty($details['billingAddress']['firstName'])) {
                $billingAddress->setFirstname($details['firstName']);
            }
            if (!empty($details['billingAddress']['lastName'])) {
                $billingAddress->setLastname($details['lastName']);
            }
            if (!empty($details['billingAddress']['email'])) {
                $billingAddress->setEmail($details['email']);
            }
        } else {
            $this->updateAddressData($billingAddress, $details['shippingAddress']);
        }

        // PayPal's address supposes not saving against customer account
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
        $extendedAddress = $addressData['extendedAddress'] ?? null;

        $address->setStreet([$addressData['streetAddress'], $extendedAddress]);
        $address->setCity($addressData['locality']);
        $address->setRegion($addressData['region']);
        $address->setCountryId($addressData['countryCodeAlpha2']);
        $address->setPostcode($addressData['postalCode']);

        // PayPal's address supposes not saving against customer account
        $address->setSaveInAddressBook(false);
        $address->setSameAsBilling(false);
        $address->setCustomerAddressId(null);
    }
}
