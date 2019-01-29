<?php

namespace Magento\Braintree\Controller\Paypal;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Braintree\Gateway\Config\PayPal\Config;
use Magento\Braintree\Model\Paypal\Helper\QuoteUpdater;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class OneClick
 * @package Magento\Braintree\Controller\Paypal
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class OneClick extends Review
{
    private $quoteFactory;
    private $productRepository;
    public function __construct(
        Context $context,
        Config $config,
        Session $checkoutSession,
        QuoteUpdater $quoteUpdater,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        parent::__construct(
            $context,
            $config,
            $checkoutSession,
            $quoteUpdater
        );
        $this->productRepository = $productRepository;
        $this->quoteFactory = $quoteFactory;
    }
    protected $quote;
    protected function getQuote()
    {
        if ($this->quote) {
            return $this->quote;
        }
        return parent::getQuote();
    }
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestData = json_decode(
            $this->getRequest()->getPostValue('result', '{}'),
            true
        );
        // @todo formkey validation
        if (!empty($requestData['additionalData'])) {
            parse_str($requestData['additionalData'], $requestData['additionalData']);
        }
        if (empty($requestData['additionalData']['product'])) {
            // @todo error
            exit;
        }
        // @todo build the new quote and have something in getQuote to load in our one only if you're on this page (what about success page?)
        $quote = $this->quoteFactory->create();
        $quote->setCustomerIsGuest(1);
        $quote->setInventoryProcessed(false);
        /**
         * @var $product \Magento\Quote\Api\Data\CartItemInterface
         */
        $product = $this->productRepository->getById(
            $requestData['additionalData']['product'],
            false,
            1, // @todo
            false
        );
        // @todo
        $additionalData = new \Magento\Framework\DataObject;
        $additionalData->setData($requestData['additionalData']);
        try {
            $quote->addProduct($product, $additionalData);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $quote->collectTotals();
        $quote->save($quote);
        $quote->setTotalsCollectedFlag(false);
        $this->checkoutSession->setBraintreeOneClickQuoteId($quote->getId());
        $this->checkoutSession->replaceQuote($quote);
        return parent::execute();
    }
}
