<?php
declare(strict_types=1);

namespace Magento\Braintree\Controller\Lpm;

use Exception;
use Magento\Braintree\Model\LocalPaymentFactory;
use Magento\Braintree\Model\ResourceModel\LocalPayment as LocalPaymentResource;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use Psr\Log\LoggerInterface;

/**
 * Class SavePaymentId
 */
class SavePaymentId extends Action
{
    /**
     * @var LocalPaymentFactory
     */
    private $localPaymentFactory;
    /**
     * @var LocalPaymentResource
     */
    private $localPaymentResource;
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;
    /**
     * @var QuoteIdMask
     */
    private $quoteIdMaskResource;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Quote\Model\QuoteIdMask
     */
    private $quoteIdMask;

    /**
     * SavePaymentId constructor.
     *
     * @param Context $context
     * @param LocalPaymentFactory $localPaymentFactory
     * @param LocalPaymentResource $localPaymentResource
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMask $quoteIdMaskResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        LocalPaymentFactory $localPaymentFactory,
        LocalPaymentResource $localPaymentResource,
        \Magento\Quote\Model\QuoteIdMask $quoteIdMaskModel,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMask $quoteIdMaskResource,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->localPaymentFactory = $localPaymentFactory;
        $this->localPaymentResource = $localPaymentResource;
        $this->quoteIdMask = $quoteIdMaskModel;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResource = $quoteIdMaskResource;
        $this->logger = $logger;
    }

    /**
     * @return ResponseInterface|ResultInterface|string
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $paymentId = $this->getRequest()->getParam('payment_id');
        $quoteIdMask = $this->getRequest()->getParam('quote_id');

        if (!$paymentId || !$quoteIdMask) {
            $response->setData(['success' => false, 'message' => __('No Payment/Quote ID set.')]);
            return $response;
        }

        $quoteIdMaskFactory = $this->quoteIdMaskFactory->create();
        $this->quoteIdMaskResource->load($quoteIdMaskFactory, $quoteIdMask, 'masked_id');
        $quoteId = $quoteIdMaskFactory->getData('quote_id');

        $localPayment = $this->localPaymentFactory->create();
        $localPaymentResource = $this->localPaymentResource->load($localPayment, null);
        $localPayment->setPaymentId($paymentId);
        $localPayment->setQuoteId($quoteId);

        try {
            $localPaymentResource->save($localPayment);
            $response->setData(['success' => true, 'message' => __('Payment ID saved.')]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $response->setData(['success' => false, 'message' => $e->getMessage()]);
        }

        return $response;
    }
}
