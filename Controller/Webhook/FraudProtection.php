<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Braintree\Controller\Webhook;

use Braintree\WebhookNotification;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use Magento\Sales\Api\Data\TransactionSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Webhook\Config;
use Magento\Sales\Api\OrderManagementInterface;
use Psr\Log\LoggerInterface;

class FraudProtection extends Action implements CsrfAwareActionInterface
{
    private const TRANSACTION_DECISION_APPROVED = 'Approve';
    private const TRANSACTION_SETTLED = 'Settled';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Http
     */
    private $httpRequest;

    /**
     * @var Config
     */
    private $webhookConfig;

    /**
     * @var BraintreeAdapter
     */
    private $adapter;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * FraudProtection constructor.
     *
     * @param Context $context
     * @param Config $webhookConfig
     * @param LoggerInterface $logger
     * @param Http $httpRequest
     * @param BraintreeAdapter $adapter
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderManagementInterface $orderManagement
     */
    public function __construct(
        Context $context,
        Config $webhookConfig,
        LoggerInterface $logger,
        Http $httpRequest,
        BraintreeAdapter $adapter,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        OrderManagementInterface $orderManagement
    ) {
        parent::__construct($context);
        $this->webhookConfig = $webhookConfig;
        $this->logger = $logger;
        $this->httpRequest = $httpRequest;
        $this->adapter = $adapter;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
    }

    /**
     * Process braintree webhook response
     *
     * @return ResultInterface|Page|null
     */
    public function execute()
    {
        if ($this->webhookConfig->isEnabled()) {
            if (!empty($webhookBody = $this->httpRequest->getPost())) {
                try {
                    $webhookResponse = WebhookNotification::parse($webhookBody['bt_signature'], $webhookBody['bt_payload']);

                    if (!empty($webhookResponse)) {
                        if ($webhookResponse->kind === WebhookNotification::TRANSACTION_REVIEWED) {
                            $this->processTransactionReviewed($webhookResponse);
                        }
                        if (in_array(
                            $webhookResponse->kind,
                            [
                                WebhookNotification::TRANSACTION_SETTLED,
                                WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED
                            ]
                        )) {
                            $this->processSettlement($webhookResponse);
                        }
                    }
                } catch (\Exception $exception) {
                    $this->logger->info("Braintree Webhook ERROR:", [
                        $exception->getMessage()
                    ]);
                }
                return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            }
        }

        return null;
    }

    /**
     * Process the 'transaction_reviewed' webhook kind
     *
     * @param $webhookResponse
     */
    protected function processTransactionReviewed($webhookResponse)
    {
        $transactionReview = $webhookResponse->transactionReview;
        $transactionData = $this->getOrderByTransaction($transactionReview->transactionId);
        if ($transactionData->getTotalCount() > 0) {
            foreach ($transactionData->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getOrderId());
                if ($transactionReview->decision === self::TRANSACTION_DECISION_APPROVED) {
                    $this->approveOrder($order, $transactionReview);
                } else {
                    $this->rejectOrder($order, $transactionReview);
                }
            }
        }
    }

    /**
     * Process the settlement webhook kind
     *
     * @param $webhookResponse
     */
    protected function processSettlement($webhookResponse)
    {
        $transactionReview = $webhookResponse->transaction;
        $transactionData = $this->getOrderByTransaction($transactionReview->id);
        if ($transactionData->getTotalCount() > 0) {
            foreach ($transactionData->getItems() as $transaction) {
                $order = $this->orderRepository->get($transaction->getOrderId());
                if ($transactionReview->status === self::TRANSACTION_SETTLED) {
                    $this->approveOrder($order, $transactionReview);
                } else {
                    $this->rejectOrder($order, $transactionReview);
                }
            }
        }
    }

    /**
     * Get Order By Transaction
     *
     * @param $transactionId
     * @return TransactionSearchResultInterface
     */
    public function getOrderByTransaction($transactionId): TransactionSearchResultInterface
    {
        $this->searchCriteriaBuilder->addFilter('txn_id', $transactionId);
        return $this->transactionRepository->getList(
            $this->searchCriteriaBuilder->create()
        );
    }

    /**
     * Approve Order
     *
     * @param $order
     * @param $transactionReview
     */
    public function approveOrder($order, $transactionReview)
    {
        $approvedStatus = $this->webhookConfig->getFraudApproveOrderStatus();
        $order->setState($approvedStatus)
            ->setStatus($approvedStatus)
            ->addStatusHistoryComment(__('Payment approved for Transaction ID: "%1". %2.', $transactionReview->transactionId, $transactionReview->reviewerNote));
        $this->orderRepository->save($order);
    }

    /**
     * Reject Order
     *
     * @param $order
     * @param $transactionReview
     */
    public function rejectOrder($order, $transactionReview)
    {
        $rejectedStatus = $this->webhookConfig->getFraudRejectOrderStatus();
        if ($rejectedStatus === 'canceled') {
            $this->orderManagement->cancel($order->getId());
        } else {
            $order->setState($rejectedStatus)->setStatus($rejectedStatus);
        }
        $order->addStatusHistoryComment(__('Payment declined for Transaction ID: "%1". %2.', $transactionReview->transactionId, $transactionReview->reviewerNote));
        $this->orderRepository->save($order);
    }

    /**
     * @inheritdoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
