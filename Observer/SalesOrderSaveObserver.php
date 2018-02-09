<?php

namespace Magento\Braintree\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Braintree\Api\Data\TransactionDetailDataInterfaceFactory;

/**
 * Class SalesOrderPlaceObserver
 * @package Magento\Braintree\Observer
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class SalesOrderSaveObserver implements ObserverInterface
{
    /**
     * @var TransactionDetailDataInterfaceFactory
     */
    protected $transactionDetailFactory;

    /**
     * SalesOrderPlaceObserver constructor.
     * @param TransactionDetailDataInterfaceFactory $transactionDetailFactory
     */
    public function __construct(
        TransactionDetailDataInterfaceFactory $transactionDetailFactory
    ) {
        $this->transactionDetailFactory = $transactionDetailFactory;
    }

    /**
     * Save additional transaction information for braintree methods
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');

        if (!$order->getId()) {
            return;
        }

        $paymentMethod = $order->getPayment()->getMethod();
        if (substr($paymentMethod, 0, 9) == 'braintree') {
            $additionalInformation = $order->getPayment()->getAdditionalInformation();
            if (!empty($additionalInformation[PaymentDetailsHandler::TRANSACTION_SOURCE])) {
                /** @var $transactionDetail \Magento\Braintree\Api\Data\TransactionDetailDataInterface */
                $transactionDetail = $this->transactionDetailFactory->create();

                // $order-isObjectNew is always false. Workaround: ensure no entries are added if one exists already
                $transactionDetail->getResource()->load($transactionDetail, $order->getId(), 'order_id');
                if (!$transactionDetail->getId()) {
                    $transactionDetail->setOrderId($order->getId());
                    $transactionDetail->setTransactionSource(
                        $additionalInformation[PaymentDetailsHandler::TRANSACTION_SOURCE]
                    );
                    $transactionDetail->getResource()->save($transactionDetail);
                }
            }
        }
    }
}
