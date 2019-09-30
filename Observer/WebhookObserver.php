<?php
declare(strict_types=1);

namespace Magento\Braintree\Observer;

use Braintree\WebhookNotification;
use Magento\Braintree\Model\LocalPaymentFactory;
use Magento\Braintree\Model\ResourceModel\LocalPayment as LocalPaymentResource;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class WebhookObserver
 */
class WebhookObserver implements ObserverInterface
{
    const WEBHOOK_KIND = [
        WebhookNotification::LOCAL_PAYMENT_COMPLETED => 'localPaymentCompleted'
    ];

    /**
     * @var LocalPaymentFactory
     */
    private $localPaymentFactory;
    /**
     * @var LocalPaymentResource
     */
    private $localPaymentResource;
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;
    /**
     * @var QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var CartManagementInterface
     */
    private $quoteManagement;
    /**
     * @var Quote\PaymentFactory
     */
    private $paymentFactory;

    /**
     * WebhookObserver constructor.
     *
     * @param LocalPaymentFactory $localPaymentFactory
     * @param LocalPaymentResource $localPaymentResource
     * @param QuoteFactory $quoteFactory
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        LocalPaymentFactory $localPaymentFactory,
        LocalPaymentResource $localPaymentResource,
        QuoteFactory $quoteFactory,
        QuoteRepository $quoteRepository,
        PaymentFactory $paymentFactory,
        CartManagementInterface $quoteManagement
    ) {
        $this->localPaymentFactory = $localPaymentFactory;
        $this->localPaymentResource = $localPaymentResource;
        $this->quoteFactory = $quoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->quoteManagement = $quoteManagement;
        $this->paymentFactory = $paymentFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $webhookData = $observer->getData('eventData');

        if (array_key_exists($webhookData->kind, self::WEBHOOK_KIND)) {
            $kind = self::WEBHOOK_KIND[$webhookData->kind];
            $this->$kind($webhookData);
        }
    }

    /**
     * @param $data
     * @return mixed
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function localPaymentCompleted($data)
    {
        $localPayment = $this->localPaymentFactory->create();
//        $this->localPaymentResource->load($localPayment, $data->subject['localPayment']['paymentId'], 'payment_id');
        $this->localPaymentResource->load($localPayment, 'PAYID-LWG7WYI79L68019YY9806021', 'payment_id');
        $quoteId = $localPayment->getQuoteId();

        /** @var Quote $quoteId */
        $quote = $this->quoteRepository->get($quoteId);

        // TODO add payment method to quote/order
        $payment = $quote->getPayment();
        $payment->setMethod('braintree_local_payment');
        $payment->setAdditionalInformation([
            'payment_method_nonce' => $data->subject['localPayment']['paymentMethodNonce']
        ]);
        $quote->setPayment($payment);

        // If no customer ID, assume quote is guest
        if (!$quote->getCustomerId()) {
            $quote->setCustomerEmail('pcanning@gmail.com');
            $quote->setCheckoutMethod('guest');
        }

        // TODO turn quote into order
        $order = $this->quoteManagement->submit($quote);

        return $data;
    }
}
