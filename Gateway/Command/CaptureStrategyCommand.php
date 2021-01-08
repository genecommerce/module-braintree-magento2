<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Command;

use Braintree\Transaction;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\ContextHelper;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Braintree\Model\Ui\PayPal\ConfigProvider as PaypalConfigProvider;

/**
 * Class CaptureStrategyCommand
 * @SuppressWarnings(PHPMD)
 */
class CaptureStrategyCommand implements CommandInterface
{
    /**
     * Braintree Intent Sale command
     */
    const SALE = 'sale';

    /**
     * Braintree capture command
     */
    const CAPTURE = 'settlement';

    /**
     * Braintree partial capture command
     */
    const PARTIAL_CAPTURE = 'partial_capture';

    /**
     * Braintree vault capture command
     */
    const VAULT_CAPTURE = 'vault_capture';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var BraintreeAdapter
     */
    private $braintreeAdapter;

    /**
     * @var BraintreeSearchAdapter
     */
    private $braintreeSearchAdapter;

    /**
     * Constructor
     *
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     * @param BraintreeAdapter $braintreeAdapter
     * @param BraintreeSearchAdapter $braintreeSearchAdapter
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader,
        BraintreeAdapter $braintreeAdapter,
        BraintreeSearchAdapter $braintreeSearchAdapter
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
        $this->braintreeAdapter = $braintreeAdapter;
        $this->braintreeSearchAdapter = $braintreeSearchAdapter;
    }

    /**
     * @inheritdoc
     * @throws NotFoundException
     */
    public function execute(array $commandSubject)
    {
        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var OrderPaymentInterface $paymentInfo */
        $paymentInfo = $paymentDO->getPayment();
        $amount = $commandSubject['amount'];
        ContextHelper::assertOrderPayment($paymentInfo);

        $command = $this->getCommand($paymentInfo, $amount);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Get execution command name
     * @param OrderPaymentInterface $payment
     * @param float $amount
     * @return string
     */
    private function getCommand(OrderPaymentInterface $payment, $amount): string
    {
        // if auth transaction is not exists execute authorize&capture command
        $existsCapture = $this->isExistsCaptureTransaction($payment);

        if (!$existsCapture && !$payment->getAuthorizationTransaction()) {
            return self::SALE;
        }

        // do partial capture for authorization transaction only braintree PayPal
        if ($amount < $payment->getAmountAuthorized() && !$this->isExpiredAuthorization($payment)
            && ($payment->getMethod() == PaypalConfigProvider::PAYPAL_CODE)) {
            return self::PARTIAL_CAPTURE;
        }

        // do capture for authorization transaction
        if (!$existsCapture && !$this->isExpiredAuthorization($payment)) {
            return self::CAPTURE;
        }

        // process capture for payment via Vault
        return self::VAULT_CAPTURE;
    }

    /**
     * @param OrderPaymentInterface $payment
     * @return boolean
     */
    private function isExpiredAuthorization(OrderPaymentInterface $payment): bool
    {
        $collection = $this->braintreeAdapter->search(
            [
                $this->braintreeSearchAdapter->id()->is($payment->getLastTransId()),
                $this->braintreeSearchAdapter->status()->is(Transaction::AUTHORIZATION_EXPIRED)
            ]
        );

        return $collection->maximumCount() > 0;
    }

    /**
     * Check if capture transaction already exists
     *
     * @param OrderPaymentInterface $payment
     * @return bool
     */
    private function isExistsCaptureTransaction(OrderPaymentInterface $payment): bool
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $count = $this->transactionRepository->getList($searchCriteria)->getTotalCount();
        return (boolean) $count;
    }
}
